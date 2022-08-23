<?php

namespace App\Controller;

use App\Repository\MoviesRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
        //Récupérer les punchouts
            $data = $moviesRepository->findBy(
                array(),
                array('titre' => 'ASC')
            );
        }

        //Pagination
        $paginationMovies = $paginator->paginate(
            $data, 
            $request->query->getInt('page', 1), 
            15
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
        $filmInfos = $moviesRepository->find($id);

        return $this->render('affiche.html.twig', [
            'pageName' => 'details_affiche_film',
            'title' => 'Affiche du Film',
            'donneesFilm' => $filmInfos,
        ]);
    }
}
