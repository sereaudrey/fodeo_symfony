<?php

namespace App\Controller;

use Exception;
use App\Repository\UsersRepository;
use Symfony\Component\Mailer\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class SecurityController extends AbstractController
{
    /**
    * @Route("/login", name="login")
    */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {

        //Afficher une erreur si mauvaise connexion
        $error = $authenticationUtils->getLastAuthenticationError();
        
        //récuperer dernier identifiant entré par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', array(
            'last_username'=> $lastUsername,
            'error'        => $error,
            'pageName'     =>'login',
            'title'        => 'Login - Fodéo'
        ));
    }

    /**
    * @Route("/logout", name="logout")
    */
    public function logout() 
    {}

    /**
    * @Route("/mdp-oublie", name="mdp-oublie")
    */
    public function mdpOublie(AuthenticationUtils $authenticationUtils) 
    {
        $error = $authenticationUtils->getLastAuthenticationError();

        return $this->render('security/mdpOublie.html.twig', array(
            'error'        => $error,
            'pageName'     =>'login',
            'title'        => 'Mot de passe oublié - Fodéo'
        ));
    }

    /**
     * @Route("/mdp-oublie-post", name ="mdp-oublie-post")
     */
    public function mdpOubliePost(Request $request, UsersRepository $usersRepository, 
    TokenGeneratorInterface $tokenGenerate, MailerInterface $mailer) 
    {
        //On récupère le mail renseigné 
        $email = $request->get('emailMdpOublie');
        //On vérifie si un utilisateur a cet email
        $user = $usersRepository->findOneBy(array('email' => $email));
        if($user) {
            $prenom = $user->getPrenom();
            //On génère un token
            $token =$tokenGenerate->generateToken();
            // On insère le token dans la bdd
            try{
                $insertToken = $usersRepository->createQueryBuilder('t')
                    ->update()
                    ->set('t.reset_token', ':resetToken')
                    ->where('t.email = :email')
                    ->setParameter('resetToken', $token)
                    ->setParameter('email', $email)
                    ->getQuery()
                    ->getResult();
            } catch(\Exception $e) {
                $this->addFlash('warning', 'Une erreur est survenue : ' . $e->getMessage());
            }
            // On génère l'url de réinitialisation de mdp
            $url = $this->generateUrl('reset-password', ['token'=> $token], UrlGeneratorInterface::ABSOLUTE_URL);
            //on prépare le mail
            $mail = (new TemplatedEmail())
                ->from('sere.audrey@gmail.com')
                ->to($user->getEmail())
                ->subject('Fodéo - Réinitialisation de votre mot de passe')
                ->htmlTemplate('security/mailResetPassword.html.twig')
                ->context([
                    'url' => $url,
                    'prenom' => $prenom
                ]);
            //Si le mail existe en base, on envoie le mail
            try {
                $mailer->send($mail);
                $this->addFlash('success', 'Si votre mail est correct, vous allez recevoir un email contenant un lien de réinitialisation dans quelques minutes');
            
            } catch(\Exception $e) {
                $this->addFlash('danger', 'Une erreur est survenue dans l\'envoi du mail. Merci de contacter l\'équipe informatique');
                throw new Exception('Erreur : '.$e->getMessage());
            }
        } else {
            $this->addFlash('success', 'Si votre mail est correct, vous allez recevoir un email contenant un lien de réinitialisation dans quelques minutes');
        }
        
        return $this->redirectToRoute('login');
    }

    /**
    * @Route("/reset-password/{token}", name="reset-password")
    */
    public function resetPassword($token) 
    {
        return $this->render('security/resetPassword.html.twig', array(
            'pageName'     =>'login',
            'title'        => 'Réinitialiser Mot de Passe - Interface Punchout',
            'token'        => $token
        ));
    }

    /**
    * @Route("/reset-password-post", name="reset-password-post")
    */
    public function resetPasswordPost(Request $request, UserPasswordEncoderInterface $encoder, EntityManagerInterface $manager, 
    UsersRepository $usersRepository) {
        // //On cherche l'utilisateur avec le token
        // $token = $request->request->get('token');
        // $user = $cockpitUsersRepository->findOneBy(array('reset_token' => $token));
        // if(!$user) {
        //     $this->addFlash('danger', 'Utilisateur inconnu');
        //     return $this->redirectToRoute('mdp-oublie');
        // }

        // //On vérifie que les deux mdp soient identiques 
        // $newMdp = $request->request->get('newMdp');
        // $confirmNewMdp = $request->request->get('confirmNewMdp');

        // if(!empty($newMdp) && !empty($confirmNewMdp)) {
            
        //     if($newMdp !== $confirmNewMdp) {
        //         //On affiche un message les mdp ne sont pas identiques
        //         $this->addFlash('danger', 'Les mots de passe ne correspondent pas. Veuillez écrire le même mot de passe');
        //         return $this->redirectToRoute('reset-password', ['token' => $token]);
        //     } else {
        //         //On chiffre le mot de passe 
        //         $mdp = $encoder->encodePassword($user, $newMdp);
        //         //On met à jour la bdd avec le nouveau mdp + supprime le token
        //         try {
        //             $manager->beginTransaction();
        //             $updateMdp = $cockpitUsersRepository->createQueryBuilder('p')
        //             ->update()
        //             ->set('p.password', ':password')
        //             ->set('p.reset_token', ':resetToken')
        //             ->where('p.reset_token = :token')
        //             ->setParameter('password', $mdp)
        //             ->setParameter('resetToken', '')
        //             ->setParameter('token', $token)
        //             ->getQuery()
        //             ->getResult();
        
        //             //On affiche un message mdp modifié
        //             $this->addFlash('success', 'Votre mot de passe a bien été modifié');
        //             $manager->flush();
        //             $manager->commit();
        
        //             return $this->redirectToRoute('login');
        //         } catch (Exception $e) {
        //             $manager->rollback();
        //             throw new Exception($e->getMessage());
        //             return $this->redirectToRoute('reset-password', ['token' => $token]);
        //         }
                
        //     }
        // } else {
        //     //Afficher message "Veuillez renseigner les champs"
        //     $this->addFlash('danger', 'Veuillez renseigner tous les champs');
        //     return $this->redirectToRoute('reset-password', ['token' => $token]);
        // }
    }
}
