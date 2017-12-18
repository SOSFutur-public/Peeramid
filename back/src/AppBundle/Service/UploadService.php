<?php
/**
 * Created by PhpStorm.
 * User: SOSF - Serveur 1
 * Date: 12/10/2017
 * Time: 09:43
 */

namespace AppBundle\Service;


use AppBundle\Entity\FileType;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadService
{
    /**
     * @param UploadedFile $file
     * @param int $maxSize
     * @param array $allowedExtensions
     * @return array
     */
    public function checkFile(UploadedFile $file, $maxSize, $allowedExtensions)
    {
        $errors = array();
        if ($file->getSize() > $maxSize * 1024 * 1024) {
            $errors[] = array(
                'message' => 'La taille du fichier est supérieure à la taille maximale (' . $maxSize . ' Mo).'
            );
        }
        if ($allowedExtensions) {
            $fileInfo = pathinfo($file->getClientOriginalName());
            preg_match('/\.(.*)/', $fileInfo['basename'], $fileExtensions);
            if (!$fileExtensions) {
                $errors[] = array('message' => 'Fichier invalide (aucune extension).');
            }

            $extension = $file->guessExtension();
            if (!$extension) {
                $extension = end(explode('.', $file->getClientOriginalName()));
            }
            $extension = strtolower($extension);

            $extensionFound = false;
            /** @var FileType $allowedExtension */
            foreach ($allowedExtensions as $allowedExtension) {
                if ($allowedExtension->getType() === $extension) {
                    $extensionFound = true;
                    if ($allowedExtension->getMime() !== $file->getMimeType()) {
                        $errors[] = array('message' => 'Type mime invalide.');
                    }
                    break;
                }
            }

            if (!$extensionFound) {
                $errors[] = array('message' => 'Extension invalide.');
            }
        }
        return array(
            'success' => empty($errors),
            'errors' => $errors
        );
    }
}