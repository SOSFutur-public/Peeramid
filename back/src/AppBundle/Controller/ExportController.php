<?php

namespace AppBundle\Controller;

use AppBundle\Constants;
use AppBundle\Entity\Assignment;
use AppBundle\Entity\AssignmentCriteria;
use AppBundle\Entity\Correction;
use AppBundle\Entity\CorrectionCriteria;
use AppBundle\Entity\CorrectionSection;
use AppBundle\Entity\Evaluation;
use AppBundle\Entity\Group;
use AppBundle\Entity\Section;
use AppBundle\Entity\User;
use AppBundle\Service\StatsService;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\FOSRestController;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use ZipArchive;


/**
 * Class ExportController
 * @package AppBundle\Controller
 * @RouteResource("export")
 *
 */
class ExportController extends FOSRestController
{
    /**
     * Get export file
     * @param $id
     * @return Response
     * @Security("is_granted('ROLE_TEACHER')")
     */
    public function getAction($id)
    {
        /** @var Evaluation $evaluation */
        $evaluation = $this->getDoctrine()->getRepository('AppBundle:Evaluation')->find($id);

        if ($evaluation === null) {
            throw new NotFoundHttpException("Cette évaluation n'existe pas");
        }

        $this->get('app.service.access_service')->tryEntity(
            $this->getUser(),
            array($evaluation->getTeacher())
        );

        $zipName = sprintf("Export_evaluation_%d_%s.zip", $evaluation->getId(), date("Ymd_his"));
        $this->buildZip($zipName, $evaluation);
        //ob_end_clean();
        $zip = fopen($zipName, "r");
        $content = stream_get_contents($zip);

        $response = new Response($content, 200, array(
            'Content-Transfer-encoding' => 'binary',
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="' . $zipName . '"',
            'Content-Length' => filesize($zipName)
        ));

        // Remove file
        fclose($zip);
        unlink($zipName);

        return $response;
    }

    private function buildZip($zipName, Evaluation $evaluation)
    {
        $fs = new Filesystem();

        // ZIP
        $zip = new ZipArchive;
        $zip->open($zipName, ZipArchive::CREATE);

        $csvContent = $this->buildCriteriaStatsCsv($evaluation);
        $csvName = sprintf("Export_evaluation_stats_%d_%s.csv", $evaluation->getId(), date("Ymd_his"));
        $zip->addFromString($csvName, $csvContent);

        $csvQuality = $this->buildQualityCsv($evaluation);
        $csvName = sprintf("Export_evaluation_quality_%d_%s.csv", $evaluation->getId(), date("Ymd_his"));
        $zip->addFromString($csvName, $csvQuality);

        // Get all assignment files
        if ($evaluation->getAssignments()) {
            $uploadDirectory = $this->getParameter('upload.directory');

            /** @var Assignment $assignment */
            foreach ($evaluation->getAssignments() as $assignment) {
                $rootPath = $uploadDirectory .
                    sprintf(Constants::ASSIGNMENT_FILE_PATH_FORMAT, $assignment->getId());

                if ($fs->exists($rootPath)) {
                    $directory = 'Assignment_' . $assignment->getId() . '_' . $this->getAuthor($assignment);
                    $zip->addEmptyDir($directory);
                    $files = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($rootPath),
                        RecursiveIteratorIterator::LEAVES_ONLY
                    );
                    foreach ($files as $name => $file) {
                        // Skip directories (they would be added automatically)
                        if (!$file->isDir()) {
                            // Get real and relative path for current file
                            $filePath = $file->getRealPath();

                            // Add current file to archive
                            $zip->addFile($filePath, $directory . '/' . $file->getFilename());
                        }
                    }
                }
            }
        }

        $zip->close();

        return $zip;
    }

    private function buildCriteriaStatsCsv(Evaluation $evaluation)
    {
        $table = array();
        /** @var Assignment $assignment */
        foreach ($evaluation->getAssignments() as $assignment) {
            /** @var Correction $correction */
            foreach ($assignment->getCorrections() as $correction) {
                if ($correction->getUser()) {
                    if ($correction->getUser()->getRole()->getId() == Constants::ROLE_TEACHER) {
                        $correctionTeacher = clone $correction;
                        break;
                    }
                }
            }
            foreach ($assignment->getCorrections() as $correction) {
                if ($correction->isStudentCorrection()) {
                    $line = array();
                    $line['Auteur'] = utf8_decode($assignment->getAuthorName());
                    $line['Correcteur'] = utf8_decode($correction->getCorrectorName());
                    $draft = $correction->getDraft();
                    /** @var ArrayCollection $correctionSections */
                    $correctionSections = $correction->getCorrectionSections();
                    // Sort correctionSections by section order
                    $iterator = $correctionSections->getIterator();
                    $iterator->uasort(function ($a, $b) {
                        /** @var CorrectionSection $a */
                        /** @var CorrectionSection $b */
                        /** @var Section $sectionA */
                        $sectionA = $a->getAssignmentSection()->getSection();
                        /** @var Section $sectionB */
                        $sectionB = $b->getAssignmentSection()->getSection();
                        return ($sectionA->getOrder() < $sectionB->getOrder() ? -1 : 1);
                    });
                    $correctionSections = new ArrayCollection(iterator_to_array($iterator));
                    $sectionIndex = 1;
                    /** @var CorrectionSection $correctionSection */
                    foreach ($correctionSections as $correctionSection) {
                        $criteriaIndex = 1;
                        // Sort correctionCriterias by criteria order
                        /** @var ArrayCollection $correctionCriterias */
                        $correctionCriterias = $correctionSection->getCorrectionCriterias();
                        $iterator = $correctionCriterias->getIterator();
                        $iterator->uasort(function ($a, $b) {
                            /** @var CorrectionCriteria $a */
                            /** @var CorrectionCriteria $b */
                            return ($a->getCriteria()->getOrder() < $b->getCriteria()->getOrder() ? -1 : 1);
                        });
                        $correctionCriterias = new ArrayCollection(iterator_to_array($iterator));
                        /** @var CorrectionCriteria $correctionCriteria */
                        foreach ($correctionCriterias as $correctionCriteria) {
                            $line[utf8_decode('Section ' . $sectionIndex . ' - Critère ' . $criteriaIndex . ' - commentaires')] =
                                $draft ? null : htmlspecialchars_decode(strip_tags(utf8_decode($correctionCriteria->getComments())));
                            $line[utf8_decode('Section ' . $sectionIndex . ' - Critère ' . $criteriaIndex . ' - note')] = $draft ? null : $this->formatNumber($correctionCriteria->getMark());
                            $line[utf8_decode('Section ' . $sectionIndex . ' - Critère ' . $criteriaIndex . ' - fiabilité')] = $draft ? null : $this->formatNumber($correctionCriteria->getReliability());
                            /** @var AssignmentCriteria $assignmentCriteria */
                            foreach ($correctionCriteria->getCriteria()->getAssignmentCriterias() as $assignmentCriteria) {
                                if ($assignmentCriteria->getAssignmentSection()->getAssignment() === $assignment) {
                                    $line[utf8_decode('Section ' . $sectionIndex . ' - Critère ' . $criteriaIndex . ' - moyenne brute')] = $this->formatNumber($assignmentCriteria->getRawMark());
                                    $line[utf8_decode('Section ' . $sectionIndex . ' - Critère ' . $criteriaIndex . ' - écart-type')] = $this->formatNumber($assignmentCriteria->getStandardDeviation());
                                    $line[utf8_decode('Section ' . $sectionIndex . ' - Critère ' . $criteriaIndex . ' - moyenne pondérée')] = $this->formatNumber($assignmentCriteria->getWeightedMark());
                                    $line[utf8_decode('Section ' . $sectionIndex . ' - Critère ' . $criteriaIndex . ' - fiabilité moyenne')] = $this->formatNumber($assignmentCriteria->getReliability());
                                    break;
                                }
                            }
                            if (isset($correctionTeacher)) {
                                foreach ($correctionTeacher->getCorrectionSections() as $correctionSectionTeacher) {
                                    /** @var CorrectionCriteria $correctionCriteriaTeacher */
                                    foreach ($correctionSectionTeacher->getCorrectionCriterias() as $correctionCriteriaTeacher) {
                                        if ($correctionCriteriaTeacher->getCriteria() === $correctionCriteria->getCriteria()) {
                                            $line[utf8_decode('Section ' . $sectionIndex . ' - Critère ' . $criteriaIndex . ' - commentaires enseignant')] =
                                                htmlspecialchars_decode(strip_tags(utf8_decode($correctionCriteriaTeacher->getComments())));
                                            $line[utf8_decode('Section ' . $sectionIndex . ' - Critère ' . $criteriaIndex . ' - note enseignant')] = $this->formatNumber($correctionCriteriaTeacher->getMark());
                                            break 2;
                                        }
                                    }
                                }
                            } else {
                                $line[utf8_decode('Section ' . $sectionIndex . ' - Critère ' . $criteriaIndex . ' - note enseignant')] = null;
                            }
                            $line[utf8_decode('Section ' . $sectionIndex . ' - Critère ' . $criteriaIndex . ' - fiabilité recalculée')] = $draft ? null : $this->formatNumber($correctionCriteria->getRecalculatedReliability());
                            $line[utf8_decode('Section ' . $sectionIndex . ' - Critère ' . $criteriaIndex . ' - note finale')] = $this->formatNumber($assignmentCriteria->getMark());
                            $criteriaIndex++;
                        }
                        $sectionIndex++;
                    }
                    $line['Note de la correction'] = $draft ? null : $this->formatNumber($correction->getMark());
                    $line[utf8_decode('Fiabilité de la correction')] = $draft ? null : $this->formatNumber($correction->getReliability());
                    $line['Moyenne brute du devoir'] = $this->formatNumber($assignment->getRawMark());
                    $line['Ecart-type'] = $this->formatNumber($assignment->getStandardDeviation());
                    $line[utf8_decode('Moyenne pondérée du devoir')] = $this->formatNumber($assignment->getWeightedMark());
                    $line[utf8_decode('Fiabilité moyenne des corrections')] = $this->formatNumber($assignment->getReliability());
                    $line['Note enseignant'] = isset($correctionTeacher) ? $this->formatNumber($correctionTeacher->getMark()) : null;
                    $line[utf8_decode('Fiabilité recalculée')] = $this->formatNumber($correction->getRecalculatedReliability());
                    $line['Note finale'] = $this->formatNumber($assignment->getMark());

                    $table[] = $line;
                }
            }
        }

        $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder(";")]);
        $csv = $serializer->encode($table, 'csv');
        return $csv;
    }

    /**
     * @param $value
     * @return null|string
     */
    private function formatNumber($value)
    {
        if (is_numeric($value)) {
            return number_format($value, 2, ',', '');
        } else {
            return null;
        }
    }

    private function buildQualityCsv(Evaluation $evaluation)
    {
        /** @var StatsService $statsService */
        $statsService = $this->container->get('app.services.stats');

        /** @var array $quality */
        $quality = $statsService->getQualityStats($evaluation);

        $table = array();

        foreach ($quality as $item) {
            $line = array();
            if ($evaluation->getIndividualCorrection()) {
                /** @var User $user */
                $user = $item['user'];
                $line['Correcteur'] = utf8_decode($user->getLastName() . ' ' . $user->getFirstName());
            } else {
                /** @var Group $group */
                $group = $item['group'];
                $line['Correcteur'] = utf8_decode($group->getName());
            }
            $index = 1;
            foreach ($item['criterias_reliability'] as $reliability) {
                $line[utf8_decode('Critère ' . $index . ' fiabilité')] = $this->formatNumber($reliability);
                $index++;
            }
            $line[utf8_decode('Fiabilité moyenne')] = $this->formatNumber($item['average_reliability']);
            $table[] = $line;
        }

        $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder(";")]);
        $csv = $serializer->encode($table, 'csv');
        return $csv;
    }

    /**
     * @param Assignment $assignment
     * @return string
     */
    private function getAuthor(Assignment $assignment)
    {
        $author = "";
        if ($assignment->getUser()) {
            $author = $assignment->getUser()->getLastName();
        } else
            if ($assignment->getGroup()) {
                $author = $assignment->getGroup()->getName();
            }
        return $author;
    }
}