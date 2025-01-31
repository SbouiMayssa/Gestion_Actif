<?php

namespace App\Controller;

use App\Entity\Actif;
use App\Form\ActifType;
use App\Repository\ActifRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use \DateTime;
use \DateTimeInterface;


#[Route('/admin')]
final class ActifController extends AbstractController
{
    #[Route('', name: 'all_actif')]
    public function index(ActifRepository $Actif): Response
    {
        return $this->render('actif/AllActif.html.twig', [
            'actifs' => $Actif->findBy(['DeletedAt' => null]),
        ]);
    }



    #[Route('/actif/add', name:'add_actif')]
    public function AddAsset(EntityManagerInterface $manager, Request $request){
        $actif = new Actif();
        $form = $this->createForm(ActifType::class, $actif);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $actif->setCreatedBy($this->getUser());
            $manager->persist($actif);
            $manager->flush();

            $this->addFlash('success', "L'actif est ajouté avec succès");
            return $this->redirectToRoute('all_actif');
        }

        return $this->render('actif/add.html.twig', [
            'form' => $form->createView(),
            'action' => 'Ajouter',
        ]);
    }


    #[Route('/actif/sort/{criteria}', name: 'sort_actif', methods: ['GET'])]
    public function sort(string $criteria, ActifRepository $actifRepository): Response
    {
         switch ($criteria) {
                case 'type':
                    $actifs = $actifRepository->findAllSortedByType();
                    break;
                case 'date':
                    $actifs = $actifRepository->findAllSortedByDateAcquisation();
                    break;
                default:
                    $actifs = $actifRepository->findActifsEnPanne();
            }
    
            return $this->render('actif/sort.html.twig', [
                'actifs' => $actifs,
            ]);
        }



    #[Route('/actif/edit/{id<\d+>}', name:'actif_edit')]
    public function editActif(EntityManagerInterface $manager, Request $request, Actif $actif, $id, Security $security){
        if ($actif->getDeletedAt() !== null) {
            $this->addFlash('error', "L'actif $id est archivé et ne peut pas être modifié.");
            return $this->redirectToRoute('all_actif');
        }

        $form = $this->createForm(ActifType::class, $actif);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($actif);
            $manager->flush();

            $this->addFlash('success', "L'actif de $id est modifié avec succès");
            return $this->redirectToRoute('all_actif');
        }

        return $this->render('actif/edit.html.twig', [
            'form' => $form->createView(),
            'actif' => $actif,
            'action' => 'Modifier',
        ]);
    }


    #[Route('/actif/delete/{id}', name: 'actif_delete')]
public function delete(Actif $actif, ActifRepository $actifRepository,$id,EntityManagerInterface $manager): Response
{

   $actif=$actifRepository->find($id);

    if(!$actif){
        $this->addFlash('error',"Actif n' existe pas ");
        return $this->redirectToRoute('all_actif');
    }
    $actif->setDeletedAt(new \DateTimeImmutable()); 
    $manager->flush();
    

    $this->addFlash('success', "L'actif a été archivé avec succès.");
    return $this->redirectToRoute('all_actif');
}


    #[Route('/actif/search', name: 'actif_s', methods: ['GET'])]
    public function SearchActif(Request $request, ActifRepository $actifRepository): Response
    {
        $query = $request->query->get('q', '');
        $actifs = []; // Ajoutez cela pour initialiser la variable

        if (!empty($query)) {
            $actifs = $actifRepository->searchByNumSerieActif($query);
        }

        return $this->render('actif/searchActif.html.twig', [
            'actifs' => $actifs,
            'query' => $query, // Assurez-vous de transmettre la valeur de la recherche
        ]);
    }


    #[Route('/filter/{etat}', name: 'actif_filter', methods: ['GET'])]
    public function filter(string $etat, ActifRepository $actifRepository): Response
    {
        $actifs = $actifRepository->findByEtat($etat);

        return $this->render('actif/filter.html.twig', [
            'actifs' => $actifs,
        ]);
    }
}
