<?php

namespace App\Controller;

use Exception;
use Dompdf\Dompdf;
use Dompdf\Options;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactController extends AbstractController
{
    /**
     * @Route("/contact", name="contact")
     */
    public function contact(): Response
    {
        return $this->render('contact.html.twig', [
            'pageName' => 'Contact',
            'title' => 'Nous contacter'
        ]);
    }

    /**
     * @Route("/contactPost", name="contactPost")
     */
    public function contactPost(Request $request, MailerInterface $mailer): Response
    {
        //On récupère les données du formulaire
        $emailContact = $request->get('email_contact');
        $sujetContact = $request->get('sujet_contact');
        $descriptionContact = $request->get('description_contact');
        
        //on prépare le mail
        $mail = (new TemplatedEmail())
        ->from('do_not_reply@fodeo.com')
        ->to('test@mailhog.local')
        ->subject('Fodéo - Demande de contact')
        ->htmlTemplate('security/mailContact.html.twig')
        ->context([
            'email_utilisateur' => $emailContact,
            'sujet' => $sujetContact,
            'description' => $descriptionContact
        ]);
        //On envoie le mail
        try {
            $mailer->send($mail);
            $this->addFlash('success', 'Votre demande à bien été transmise à l\'équipe d\'administrateur.');
        
        } catch(\Exception $e) {
            $this->addFlash('danger', 'Une erreur est survenue dans l\'envoi du mail. Merci de réessayer plus tard');
            throw new Exception('Erreur : '.$e->getMessage());
        }

        return $this->redirectToRoute('contact');

    }
}