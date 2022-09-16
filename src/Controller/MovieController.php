<?php

namespace App\Controller;

use App\Entity\Movies;
use Exception;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Repository\MoviesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Endroid\QrCode\Builder\BuilderInterface;

class MovieController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function catalogue(PaginatorInterface $paginator, Request $request, MoviesRepository $moviesRepository): Response
    {
        //Barre de recherche
        $query=htmlentities($request->get('q'));
        if ($query!='') {
            $data = $moviesRepository->createQueryBuilder('m')
                        ->andWhere('m.titre LIKE :search')
                        ->setParameter('search', '%'.$query.'%')
                        ->OrderBy('m.titre', 'ASC')
                        ->getQuery()
                        ->getResult();
        } else {
        //Récupérer les films
            $data = $moviesRepository->findBy(
                array(),
                array('titre' => 'ASC')
            );
        }

        //Pagination
        $paginationMovies = $paginator->paginate(
            $data, 
            $request->query->getInt('page', 1), 
            18
        );

        return $this->render('catalogue.html.twig', [
            'pageName' => 'Catalogue',
            'title' => 'Catalogue',
            'query' => $query,
            'filmListe' => $paginationMovies
        ]);
    }

    /**
    * @Route("/affiche", name="affiche")
    */
    public function afficheFilm(Request $request, MoviesRepository $moviesRepository): Response
    {
        // Récupérer l'id de l'URL
        $id = $request->get('id');
        // Récupère les données du film via l'id
        $filmInfos = $moviesRepository->find($id);
        $bande_annonce = $filmInfos->getBandeAnnonce();
        //Pouvoir visionner la bande annonce 
        $afficheBA = $bande_annonce;
        $lien_a_enlever = "watch?v=";
        $lien_a_remplacer = "embed/";
        //On remplace les chaines de caractères dans le lien de la BA
        $afficheBA = str_replace($lien_a_enlever, $lien_a_remplacer, $afficheBA);
        //Transforme la chaine en tableau
        $afficheBA = explode("&",$afficheBA);
        $afficheBA = $afficheBA[0];

        return $this->render('affiche.html.twig', [
            'pageName' => 'details_affiche_film',
            'title' => 'Affiche du Film',
            'donneesFilm' => $filmInfos,
            'qrCodeUrl' => $bande_annonce,
            'afficheBA' => $afficheBA,

        ]);
    }

    /**
     * @Route("/deleteMovie", name="movieDelete")
     */
    public function deleteMovie(Request $request, EntityManagerInterface $manager, MoviesRepository $moviesRepository)
    {
        $id = $request->get('id');
        $movieInfos = $moviesRepository->find($id);

        $manager->beginTransaction();

        try {
            //Supprime dans la table movie
            $deleteMovie = $moviesRepository->createQueryBuilder('u')
            ->delete()
            ->where('u.id =:id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();

            $return['message'] = "Le film à bien été supprimé";
            $return['success'] = true;

            $this->addFlash('success', 'Le film à bien été supprimé');
            $manager->flush();
            $manager->commit();

            return $this->redirectToRoute('home');

        }catch(Exception $e) {
            $this->addFlash('danger', 'Une erreur est survenue lors de la suppression de l\'utilisateur');
            $manager->rollback();
            $return['message'] = $e->getMessage();
        }
        return new JsonResponse($return);
    }

    /**
     * @Route("/pdf", name="pdf")
     */
    public function createPdf(Request $request, MoviesRepository $moviesRepository) {
        $id = $request->get('id');
        $donneesFilm = $moviesRepository->find($id);
        $bande_annonce = $donneesFilm->getBandeAnnonce();
        //Pouvoir visionner la bande annonce 
        $afficheBA = $bande_annonce;
        $lien_a_enlever = "watch?v=";
        $lien_a_remplacer = "embed/";
        //On remplace les chaines de caractères dans le lien de la BA
        $afficheBA = str_replace($lien_a_enlever, $lien_a_remplacer, $afficheBA);
        //Transforme la chaine en tableau
        $afficheBA = explode("&",$afficheBA);
        $afficheBA = $afficheBA[0];
        //on définit les options du pdf
        $optionsPdf = new Options();
        //Police par défaut
        $optionsPdf->set('defaultFont', 'Roboto');
        $optionsPdf->setIsRemoteEnabled(true);

        //On instancie le pdf
        $dompdf = new Dompdf($optionsPdf);
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => FALSE,
                'verify_peer_name' => FALSE,
                'allow_self_signed' => TRUE
            ]
            ]);
        $dompdf->setHttpContext($context);
        
        //On génère le html
        $html = $this->renderView('pdf.html.twig', [
            'donneesFilm' => $donneesFilm,
            'qrCodeUrl' => $afficheBA
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        //On génère un nom de fichier
        $fichier = "Données_du_film_". $donneesFilm->getTitre() . '.pdf';

        //On envoie le pdf au navigateur
        $dompdf->stream($fichier, [
            'Attachement' => true
        ]);

        return new Response();
    }
}
