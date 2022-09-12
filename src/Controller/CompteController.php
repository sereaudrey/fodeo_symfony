<?php

namespace App\Controller;

use DateTime;
use Exception;
use App\Entity\Users;
use App\Entity\Movies;
use App\Repository\UsersRepository;
use App\Repository\MoviesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CompteController extends AbstractController
{
    /**
     * @Route("/dashboardUser", name="dashboardUser")
     */
    public function dashboardUser(): Response
    {
        return $this->render('dashboard.html.twig', [
            'pageName' => 'DashboardUser',
            'title' => 'Mon compte',
        ]);
    }

    /**
     * @Route("/modifDoneesUserProfil", name="modifDoneesUserProfil")
     */
    public function updateDonnees(Request $request, UsersRepository $usersRepository , EntityManagerInterface $manager)
    {
        $return['error'] = false;
        $id = $request->get('idUser');
        $donneesUser = $usersRepository->find($id);
        $avatarBdd = $donneesUser->getImgProfil();

        //On récupère les données du formulaire
        $avatar = "img/user/".$request->get('avatar');
        $nom = $request->get('new_nom');
        $prenom = $request->get('new_prenom');
        $pseudo = $request->get('new_pseudo');
        $date = new \DateTime('now');

        //Si l'user a une pdp autre que par defaut et qu'il ne la modifie pas on la garde
        if($avatar == "img/user/iconUser.png" && $avatar != $avatarBdd) {
            $avatar = $avatarBdd;
        };

        $manager->beginTransaction();

        try {
            if(
                empty($nom) ||
                empty($prenom) ||
                empty($pseudo)
            ) {
                $this->addFlash('danger', 'Tous les champs ne sont pas renseignés');
                throw new Exception('Tous les champs ne sont pas renseignés');
            } else {
                $user = $usersRepository->createQueryBuilder('u')
                        ->update()
                        ->set('u.img_profil', ':avatar')
                        ->set('u.nom', ':nom')
                        ->set('u.prenom', ':prenom')
                        ->set('u.pseudo', ':pseudo')
                        ->set('u.date_derniere_modif', ':date')
                        ->where('u.id = :id')
                        ->setParameter('id', $id)
                        ->setParameter('avatar', $avatar)
                        ->setParameter('nom', $nom)
                        ->setParameter('prenom', $prenom)
                        ->setParameter('pseudo', $pseudo)
                        ->setParameter('date', $date)
                        ->getQuery()
                        ->getResult();
                $this->addFlash('success', 'Vos modifications ont bien été sauvegardées');
                $manager->flush();
                $manager->commit();

                $return['message'] = "Vos modifications ont bien été sauvegardées";
                $return['success'] = true;
            }

        } catch(Exception $e) {
            $manager->rollback();
            $return['message'] = $e->getMessage();
        }

        return new JsonResponse($return);
    }

    /**
     * @Route("/modifDoneesUserMdpProfil", name="modifDoneesUserMdpProfil")
     */
    public function updateMdp(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hash ,UsersRepository $usersRepository)
    {
        //On récupère les données du formulaire
        $id = $request->get('idUser');
        $mdp = $request->get('new_mdp');
        $confirm = $request->get('new_mdp_confirm');
        $date = new \DateTime('now');

        $manager->beginTransaction();

        try {

            if(
                empty($mdp) ||
                empty($confirm)
            ) {
                $this->addFlash('danger', 'Tous les champs ne sont pas renseignés');
                throw new Exception('Tous les champs ne sont pas renseignés');
            } else {
                //On vérifie que la confirmation du mdp est bonne
                if($mdp != $confirm){
                    $this->addFlash('danger', 'Les mots de passe ne correspondent pas.');
                    throw new Exception('Les mots de passe ne correspondent pas.');
                }
                //on chiffre le mdp
                $newUser = new Users($id, $mdp);
                $hash = $hash->hashPassword($newUser, $mdp);
                //On insère en base 
                $user = $usersRepository->createQueryBuilder('m')
                        ->update()
                        ->set('m.password', ':mdp')
                        ->set('m.date_derniere_modif', ':date')
                        ->where('m.id = :id')
                        ->setParameter('id', $id)
                        ->setParameter('mdp', $hash)
                        ->setParameter('date', $date)
                        ->getQuery()
                        ->getResult();
                $this->addFlash('success', 'Votre mot de passe a bien été sauvegardé');
                $manager->flush();
                $manager->commit();

                $return['message'] = "Votre mot de passe a bien été sauvegardé";
                $return['success'] = true;
                return $this->redirectToRoute('home');
            }
        } catch(Exception $e) {
            $manager->rollback();
            $return['message'] = $e->getMessage();
            return $this->redirectToRoute('dashboardUser');
        }
    }
}