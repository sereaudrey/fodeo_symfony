<?php

namespace App\Controller;

use Exception;
use App\Entity\Users;
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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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
                ->from('do_not_reply@fodeo.com')
                ->to('test@mailhog.local')
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
            'title'        => 'Réinitialiser Mot de Passe - Fodéo',
            'token'        => $token
        ));
    }

    /**
    * @Route("/reset-password-post", name="reset-password-post")
    */
    public function resetPasswordPost(Request $request, UserPasswordHasherInterface $hash, EntityManagerInterface $manager, 
    UsersRepository $usersRepository) {
        //On cherche l'utilisateur avec le token
        $token = $request->request->get('token');
        $user = $usersRepository->findOneBy(array('reset_token' => $token));
        if(!$user) {
            $this->addFlash('danger', 'Utilisateur inconnu');
            return $this->redirectToRoute('mdp-oublie');
        }

        //On vérifie que les deux mdp soient identiques 
        $newMdp = $request->request->get('newMdp');
        $confirmNewMdp = $request->request->get('confirmNewMdp');

        if(!empty($newMdp) && !empty($confirmNewMdp)) {
            
            if($newMdp !== $confirmNewMdp) {
                //On affiche un message les mdp ne sont pas identiques
                $this->addFlash('danger', 'Les mots de passe ne correspondent pas. Veuillez écrire le même mot de passe');
                return $this->redirectToRoute('reset-password', ['token' => $token]);
            } else {
                //On chiffre le mot de passe 
                $mdp = $hash->hashPassword($user, $newMdp);
                //On met à jour la bdd avec le nouveau mdp + supprime le token
                try {
                    $manager->beginTransaction();
                    $updateMdp = $usersRepository->createQueryBuilder('p')
                    ->update()
                    ->set('p.password', ':password')
                    ->set('p.reset_token', ':resetToken')
                    ->where('p.reset_token = :token')
                    ->setParameter('password', $mdp)
                    ->setParameter('resetToken', '')
                    ->setParameter('token', $token)
                    ->getQuery()
                    ->getResult();
        
                    //On affiche un message mdp modifié
                    $this->addFlash('success', 'Votre mot de passe a bien été modifié');
                    $manager->flush();
                    $manager->commit();
        
                    return $this->redirectToRoute('login');
                } catch (Exception $e) {
                    $manager->rollback();
                    throw new Exception($e->getMessage());
                    return $this->redirectToRoute('reset-password', ['token' => $token]);
                }
                
            }
        } else {
            //Afficher message "Veuillez renseigner les champs"
            $this->addFlash('danger', 'Veuillez renseigner tous les champs');
            return $this->redirectToRoute('reset-password', ['token' => $token]);
        }
    }

    /**
    * @Route("/inscription", name="inscription")
    */
    public function inscription() 
    {
        return $this->render('security/inscription.html.twig', array(
            'pageName'     =>'login',
            'title'        => 'Inscription - Fodéo',
        ));
    }

    /**
    * @Route("/createUser", name="createUser")
    */
    public function createUser(Request $request, UserPasswordHasherInterface $hash, EntityManagerInterface $manager, 
    UsersRepository $usersRepository, TokenGeneratorInterface $tokenGenerate, MailerInterface $mailer) 
    {
       $email = $request->get('email_newUser');
       $username = $request->get('username');
       $prenom = $request->get('prenom_newUser');
       $nom = $request->get('nom_newUser');
       $mdp = $request->get('mdp_newUser');
       $role = 'Utilisateur';
       $dateCreation = new \DateTime('now');


       $manager->beginTransaction();

       try {
        if(
            empty($email) ||
            empty($mdp) ||
            empty($username) ||
            empty($nom) ||
            empty($prenom)
        ) {
            $this->addFlash('danger', 'Tous les champs obligatoires ne sont pas renseignés');
            throw new Exception('Tous les champs obligatoires ne sont pas renseignés');
        } else {
            //On vérifie si un utilisateur a déjà ce mail
            $userBdd = $usersRepository->findOneBy(array('email' => $email));
            if($userBdd) {
                $this->addFlash('danger', 'Cet utilisateur existe déjà');
                throw new Exception('Cet utilisateur existe déjà');
            }  
            //On génère un token
            $token =$tokenGenerate->generateToken();
            $newUser = new Users($email, $mdp);
            $hash = $hash->hashPassword($newUser, $mdp);
            $newUser->setEmail($email)
                    ->setPassword($hash)
                    ->setDateCreation($dateCreation)
                    ->setNom($nom)
                    ->setPrenom($prenom)
                    ->setPseudo($username)
                    ->setRole($role)
                    ->setActiveToken($token);
            $manager->persist($newUser);
            $manager->flush();
            $manager->commit();

            // On génère l'url d'activation du compte
            $url = $this->generateUrl('activer-compte', ['token'=> $token], UrlGeneratorInterface::ABSOLUTE_URL);
            //on prépare le mail
            $mail = (new TemplatedEmail())
                ->from('do_not_reply@fodeo.com')
                ->to('test@mailhog.local')
                ->subject('Fodéo - Activer votre compte')
                ->htmlTemplate('security/mailActiveCompte.html.twig')
                ->context([
                    'url' => $url,
                    'prenom' => $prenom
                ]);
            try {
                $mailer->send($mail);
                $this->addFlash('success', 'Votre compte a bien été créé. Un email avec un lien de confirmation vous a été envoyé');
                $return['message'] = 'Votre compte a bien été créé';
            } catch(\Exception $e) {
                $this->addFlash('danger', 'Une erreur est survenue dans l\'envoi du mail. Merci de contacter l\'équipe informatique');
                throw new Exception('Erreur : '.$e->getMessage());
            }
        }
            
       }catch(Exception $e) {
        $manager->rollBack();
        $return['success'] = false;
        $return['message'] = $e->getMessage();
       }

        return $this->redirectToRoute('login');
    }

    /**
    * @Route("/activer-compte{token}", name="activer-compte")
    */
    public function ActiverCompte($token, UsersRepository $users, EntityManagerInterface $manager)
    {
        // On vérifie si un utilisateur avec ce token existe dans la base de données 
        $user = $users->findOneBy(['active_token' => $token]); 
        // Si aucun utilisateur n'est associé à ce token 
        if(!$user){ 
            // On renvoie une erreur 404 
            throw $this->createNotFoundException('Cet utilisateur n\'existe pas'); 
        } 
        // On supprime le token 
        $user->setActiveToken(null); 
        $manager->persist($user); 
        $manager->flush(); 
        // On génère un message 
        $this->addFlash('success', 'Votre compte a bien été activé !'); 
        // On retourne à l'accueil 
        return $this->redirectToRoute('home');
    }
}