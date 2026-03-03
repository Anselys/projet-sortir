<?php

namespace App\Controller\Admin;

use App\Entity\Participant;
use App\Entity\Site;
use App\Form\InscriptionCSVType;
use App\Form\InscriptionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin', name: 'app_admin')]
final class InscriptionController extends AbstractController
{
    #[Route('/inscription', name: '_inscription')]
    public function inscription(Request $request, UserPasswordHasherInterface $participantPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $participant = new Participant();
        $form = $this->createForm(InscriptionType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $participant->setPassword($participantPasswordHasher->hashPassword($participant, $plainPassword));

            $entityManager->persist($participant);
            $entityManager->flush();

            // do anything else you need here, like send an email

            $this->addFlash('success', 'Nouvel utilisateur inscrit avec succès !');
            return $this->redirectToRoute('app_admin_utilisateur');
        }

        return $this->render('inscription/inscription.html.twig', [
            'inscription_form' => $form,
        ]);
    }

    #[Route('/inscription/CSV', name: '_inscription_CSV')]
    public function inscriptionCSV(Request $request, EntityManagerInterface $em): Response
    {
        $csvForm = $this->createForm(InscriptionCSVType::class);
        $csvForm->handleRequest($request);
        if ($csvForm->isSubmitted() && $csvForm->isValid()) {

            $file = $csvForm->get('submitFile')->getData();

            // Open the file
            if (($handle = fopen($file->getPathname(), "r")) !== false) {
                // Read and process the lines.
                // Skip the first line if the file includes a header
                while (($data = fgetcsv($handle)) !== false) {
                    // Do the processing: Map line to entity, validate if needed
                    $participant = new Participant();
                    // TODO: check if correct data in column
                    $participant->setEmail($data[0]);
                    $participant->setPseudo($data[1]);
                    $participant->setNom($data[2]);
                    $participant->setPrenom($data[3]);
                    $participant->setTelephone($data[4]);
                    $participant->setSite($em->getRepository(Site::class)->findOneBy(array('nom' => $data[5])));

                    $participant->setPassword('DEFAULT');
                    $participant->setIsAdmin(false);
                    $participant->setIsActif(true);

                    $em->persist($participant);
                }
                fclose($handle);
                $em->flush();
                $this->addFlash('success', 'Les profils ont été importés');
                return $this->redirectToRoute('app_admin_utilisateur');
            }
        }
        return $this->render('inscription/inscription_csv.html.twig', [
            'csv_form' => $csvForm,
        ]);
    }

}
