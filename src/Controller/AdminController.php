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

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function dashboard(): Response
    {
        return $this->render('admin/dashboardAdmin.html.twig', [
            'pageName' => 'DashboardAdmin',
            'title' => 'Espace Administrateur',
        ]);
    }

    /**
     * @Route("/admin_users", name="admin_users")
     */
    public function UsersListe(PaginatorInterface $paginator, Request $request, UsersRepository $usersRepository): Response
    {
        //Barre de recherche
        $query=htmlentities($request->get('q'));
        if ($query!='') {
            $data = $usersRepository->createQueryBuilder('a')
                        ->where('a.nom LIKE :search OR a.prenom LIKE :search OR a.email LIKE :search OR a.pseudo LIKE :search')
                        ->setParameter('search', '%'.$query.'%')
                        ->OrderBy('a.nom', 'ASC')
                        ->getQuery()
                        ->getResult();
        } else {
        //Récupérer les utilisateurs
            $data = $usersRepository->findBy(
                array(),
                array('nom' => 'ASC')
            );
        }

        //Pagination
        $paginationUsers = $paginator->paginate(
            $data, 
            $request->query->getInt('page', 1), 
            15
        );
        return $this->render('admin/users.html.twig', [
            'pageName' => 'Users',
            'title' => 'Liste des utilisateurs',
            'userListe' => $paginationUsers,
            'query' => $query,
        ]);
    }

     /**
     * @Route("/admin_user", name="user")
     */
    public function User(Request $request, UsersRepository $usersRepository): Response
    {
        // Récupérer l'id de l'URL
        $id = $request->get('id');
        $userInfos = $usersRepository->find($id);

        return $this->render('admin/user.html.twig', [
            'pageName' => 'User',
            'title' => 'Détail utilisateur',
            'donneesUser' => $userInfos,
        ]);
    }

    /**
     * @Route("/user/edit", name="userEdit")
     */
    public function EditUser(Request $request, UserPasswordHasherInterface $encoder, EntityManagerInterface $manager, UsersRepository $usersRepository)
    {
        //On récupère les données du formulaire
        $id = $request->get('id_user');
        $email = $request->get('email');
        $nom = $request->get('nom');
        $prenom = $request->get('prenom');
        $pseudo = $request->get('pseudo');
        $password = $request->get('password');
        $role = $request->get('role');
        $date = new \DateTime('now');
        $manager->beginTransaction();

        try {
            if(
                empty($email) ||
                empty($nom) ||
                empty($prenom) ||
                empty($pseudo)
            ) {
                $this->addFlash('danger', 'Tous les champs ne sont pas renseignés');
                throw new Exception('Tous les champs ne sont pas renseignés');
            } 
            
            if(!empty($password) && $password!= ''){
                //On insère en base
                $user = new Users($email, $password);
                $hash = $encoder->hashPassword($user, $password);
                $userUpdate = $usersRepository->createQueryBuilder('e')
                ->update()
                ->set('e.email', ':email')
                ->set('e.nom', ':nom')
                ->set('e.prenom', ':prenom')
                ->set('e.pseudo', ':pseudo')
                ->set('e.password', ':password')
                ->set('e.date_derniere_modif', ':date')
                ->set('e.role', ':role')
                ->where('e.id = :id')
                ->setParameter('id', $id)
                ->setParameter('role', $role)
                ->setParameter('date', $date)
                ->setParameter('password', $hash)
                ->setParameter('prenom', $prenom)
                ->setParameter('pseudo', $pseudo)
                ->setParameter('nom', $nom)
                ->setParameter('email', $email)
                ->getQuery()
                ->getResult();
            } else {
                $userUpdate = $usersRepository->createQueryBuilder('e')
                ->update()
                ->set('e.email', ':email')
                ->set('e.nom', ':nom')
                ->set('e.prenom', ':prenom')
                ->set('e.date_derniere_modif', ':date')
                ->set('e.pseudo', ':pseudo')
                ->set('e.role', ':role')
                ->where('e.id = :id')
                ->setParameter('id', $id)
                ->setParameter('role', $role)
                ->setParameter('date', $date)
                ->setParameter('prenom', $prenom)
                ->setParameter('nom', $nom)
                ->setParameter('pseudo', $pseudo)
                ->setParameter('email', $email)
                ->getQuery()
                ->getResult();
            }

            $this->addFlash('success', 'L\'utilisateur a bien été modifié');
            $return['message'] = 'L\'utilisateur a bien été modifié';
            $manager->flush();
            $manager->commit();
        } catch(\Exception $e) {
            $manager->rollback();
            $return['message'] = $e->getMessage();
        }
        return new JsonResponse($return);
    }

    /**
     * @Route("/userDelete", name="userDelete")
     */
    public function deleteUser(Request $request, EntityManagerInterface $manager, UsersRepository $usersRepository)
    {
        $id = $request->get('id');
        $userInfos = $usersRepository->find($id);

        $manager->beginTransaction();

        try {
            //Supprime dans la table users
            $deleteUser = $usersRepository->createQueryBuilder('u')
            ->delete()
            ->where('u.id =:id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();

            $return['message'] = "L'utilisateur à bien été supprimé";
            $return['success'] = true;

            $this->addFlash('success', 'L\'utilisateur à bien été supprimé');
            $manager->flush();
            $manager->commit();

            return $this->redirectToRoute('admin_users');

        }catch(Exception $e) {
            $this->addFlash('danger', 'Une erreur est survenue lors de la suppression de l\'utilisateur');
            $manager->rollback();
            $return['message'] = $e->getMessage();
        }
        return new JsonResponse($return);
    }

    /**
     * @Route("/admin_movie", name="admin_movie")
     */
    public function AddMovie(): Response
    {
        return $this->render('admin/formAddMovie.html.twig', [
            'pageName' => 'AddMovie',
            'title' => 'Ajouter du contenu au catalogue',
        ]);
    }

    /**
     * @Route("/add-movieRequest", name="add-movieRequest")
     */
    public function AddMovieRequest(Request $request, EntityManagerInterface $manager, MoviesRepository $moviesRepository): Response
    {
        $type = $request->get('add_type');
        $titre = $request->get('add_titre');
        $affiche =$request->get('add_affiche');
        $genre = $request->get('add_genre');
        $dateSortie = $request->get('add_date');
        $synopsis = $request->get('add_synopsis');
        $realisateurs = $request->get('add_realisateurs');
        $acteurs = $request->get('add_acteurs');
        $note = $request->get('add_note');
        $plateforme = $request->get('add_plateforme');

        //dd($type, $titre, $affiche, $genre, $dateSortie, $synopsis, $realisateurs, $acteurs, $note, $plateforme);

        $manager->beginTransaction();

        try {
            //On vérifie si le formulaire est bien rempli
            if(
                empty($type) ||
                empty($titre) ||
                empty($affiche) ||
                empty($acteurs) ||
                empty($synopsis) 
            ){
                $this->addFlash('danger', 'Tous les champs obligatoires ne sont pas renseignés');
                throw new Exception('Tous les champs obligatoires ne sont pas renseignés');
            } else{
                //on vérifie si un film existe déja
                $titreBdd = $moviesRepository->findOneBy(array('titre' => $titre));
                if($titreBdd) {
                    $this->addFlash('danger', 'Ce film existe déjà');
                    throw new Exception('Ce film existe déjà');
                }
                //S'il existe pas on insère
                $newMovie = new Movies();
                $newMovie->setType($type)
                        ->setTitre($titre)
                        ->setAffiche($affiche)
                        ->setGenre($genre)
                        ->setDateSortie($dateSortie)
                        ->setRealisateurs($realisateurs)
                        ->setActeurs($acteurs)
                        ->setSynopsis($synopsis)
                        ->setNote($note)
                        ->setDispoPlateforme($plateforme);
                $manager->persist($newMovie);
                $manager->flush();
                $manager->commit();
                $lastId = $newMovie->getId();
                
                $this->addFlash('success', 'Le film a bien été créé');
                $return['message'] = "Le film a bien été créé";
                $return['success'] = true;
                $return['lastID'] = $lastId;
            }
        } catch (\Exception $e) {
            $manager->rollBack();
            $return['success'] = false;
            $return['message'] = $e->getMessage();
        } 
        return new JsonResponse($return);
    }

}
