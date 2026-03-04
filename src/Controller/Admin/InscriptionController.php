<?php

namespace App\Controller\Admin;

use App\Entity\Participant;
use App\Entity\Site;
use App\Form\InscriptionCSVType;
use App\Form\InscriptionType;
use Container3xsNsFD\getValidator_EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function PHPUnit\Framework\throwException;

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
    public function inscriptionCSV(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): Response
    {
        $csvForm = $this->createForm(InscriptionCSVType::class);
        $csvForm->handleRequest($request);
        if ($csvForm->isSubmitted() && $csvForm->isValid()) {

            $file = $csvForm->get('submitFile')->getData();

            // Open the file
            if (($handle = fopen($file->getPathname(), "r")) !== false) {
                $index = 0;
                $siteError = false;
                fgetcsv($handle);
                while (($data = fgetcsv($handle, 1500, ',')) !== false) {
                    if(count($data) <= 1) {
                        $this->addFlash('danger', "Votre fichier CSV n'est pas valide, verifiez son format. (Il doit utiliser des ',' comme séparateurs.");
                        return $this->redirectToRoute('app_admin_inscription_CSV');
                    }
                    $participant = new Participant();
                    $participant->setEmail($data[0]);
                    $participant->setPseudo($data[1]);
                    $participant->setNom($data[2]);
                    $participant->setPrenom($data[3]);
                    $data[4] = preg_replace('/\s+/', '', $data[4]);
                    if ($data[4][0] != 0 and $data[4][0] != "+") {
                        $data[4] = '0' . $data[4];
                    }
                    $participant->setTelephone($data[4]);
                    $site = $em->getRepository(Site::class)->findOneBy(array('nom' => $data[5]));
                    if($site == null){
                        $siteError = true;
                    }
                    $participant->setSite($site);

                    $participant->setPassword('DEFAULT');
                    $participant->setIsAdmin(false);
                    $participant->setIsActif(true);

                    $errors = $validator->validate($participant);

                    if (count($errors) > 0 or $siteError) {
                        $this->addFlash('danger',"Il y a une erreur sur la ligne " . $index +1 . " de votre CSV.");
                        foreach($errors as $error) {
                            if($error->getPropertyPath() == "email") {
                                $this->addFlash('danger', "Verifiez la colonne email, " . $error->getInvalidValue() . " n'est pas conforme.");
                            }
                            if($error->getPropertyPath() == "nom") {
                                $this->addFlash('danger', "Verifiez la colonne nom, " . $error->getInvalidValue() . " n'est pas conforme.");
                            }
                            if($error->getPropertyPath() == "prenom") {
                                $this->addFlash('danger', "Verifiez la colonne prenom, " . $error->getInvalidValue() . " n'est pas conforme.");
                            }
                            if($error->getPropertyPath() == "telephone") {
                                $this->addFlash('danger', "Verifiez la colonne téléphone, " . $error->getInvalidValue() . " n'est pas conforme.");
                            }
                        }
                        if($siteError) {
                            $this->addFlash('danger', "Verifiez la colonne Campus, " . $error->getInvalidValue() . " n'est pas conforme.");
                        }
                        return $this->redirectToRoute('app_admin_inscription_CSV');
                    }

                    $em->persist($participant);
                    $index++;
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
