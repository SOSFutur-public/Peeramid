<?php

namespace AppBundle\Controller;

use AppBundle\Constants;
use AppBundle\Entity\PasswordToken;
use AppBundle\Entity\Role;
use AppBundle\Entity\Setting;
use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use AppBundle\Repository\FileTypeRepository;
use AppBundle\Repository\GroupRepository;
use AppBundle\Repository\LessonRepository;
use AppBundle\Repository\PasswordTokenRepository;
use AppBundle\Repository\RoleRepository;
use AppBundle\Repository\SettingRepository;
use AppBundle\Repository\UserRepository;
use AppBundle\Service\CryptService;
use AppBundle\Service\FormatService;
use AppBundle\Service\ValidatorService;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\Serializer\SerializationContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * Class UserController
 * @package AppBundle\Controller
 * @RouteResource("User")
 */
class UserController extends FOSRestController
{
    const lower = 'azertyuiopqsdfghjklmwxcvbn';
    const upper = 'AZERTYUIOPQSDFGHJKLMWXCVBN';
    const numbers = '0123456789';
    const symbols = ',;.:+=?!@#$%&^*-_';

    /**
     * @Security("is_granted('ROLE_ADMIN')")
     * @return Response
     */
    public function cgetStudentsAction()
    {
        /** @var RoleRepository $roleRepository */
        $roleRepository = $this->getDoctrine()->getRepository('AppBundle:Role');
        /** @var Role $role */
        $role = $roleRepository->find(Constants::ROLE_STUDENT);
        /** @var UserRepository $userRepository */
        $userRepository = $this->getDoctrine()->getRepository('AppBundle:User');
        $users = $userRepository->findByRole($role);
        $json = $this->get('serializer')->serialize(
            $users,
            'json',
            SerializationContext::create()->setGroups(array('id', 'admin-user-list'))
        );
        return new Response($json);
    }

    /**
     * @Security("is_granted('ROLE_ADMIN')")
     * @return Response
     */
    public function cgetTeachersAction()
    {
        /** @var RoleRepository $roleRepository */
        $roleRepository = $this->getDoctrine()->getRepository('AppBundle:Role');
        /** @var Role $role */
        $role = $roleRepository->find(Constants::ROLE_TEACHER);
        /** @var UserRepository $userRepository */
        $userRepository = $this->getDoctrine()->getRepository('AppBundle:User');
        $users = $userRepository->findByRole($role);
        $json = $this->get('serializer')->serialize(
            $users,
            'json',
            SerializationContext::create()->setGroups(array('id', 'admin-user-list'))
        );
        return new Response($json);
    }

    /**
     * @return Response
     *
     * @Get("/users/loggedIn")
     */
    public function getLoggedAction()
    {
        $user = $this->getUser();

        $json = $this->get('serializer')->serialize(
            $user,
            'json',
            SerializationContext::create()->setGroups(array('id', 'user-light'))
        );

        return new Response($json);
    }

    /**
     * Gets a user by Id
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_TEACHER')")
     * @param integer $id
     * @return Response
     */
    public function getAction($id)
    {
        /** @var User $user */
        $user = $this->getDoctrine()->getRepository('AppBundle:User')->find($id);
        $json = $this->get('serializer')->serialize(
            $user,
            'json',
            SerializationContext::create()->setGroups(array('id', 'admin-user-edit'))
        );
        return new Response($json);
    }

    /**
     * Get all lessons for a user
     * @Security("is_granted('ROLE_STUDENT') or is_granted('ROLE_TEACHER')")
     * @return Response
     */
    public function getLessonsAction()
    {
        /** @var User $user */
        $user = $this->getUser();
        /** @var LessonRepository $lessonRepository */
        $lessonRepository = $this->getDoctrine()->getRepository('AppBundle:Lesson');
        $lessons = $lessonRepository->findByUser($user->getId());
        $json = $this->get('serializer')->serialize(
            $lessons,
            'json',
            SerializationContext::create()->setGroups(array('id', 'lesson-list'))
        );
        return new Response($json);
    }

    /** Find Groups of a user
     * @Security("is_granted('ROLE_STUDENT')")
     * @return Response
     */
    public function getGroupsAction()
    {
        /** @var User $user */
        $user = $this->getUser();
        /** @var GroupRepository $groupRepository */
        $groupRepository = $this->getDoctrine()->getRepository('AppBundle:Group');
        $groups = $groupRepository->findGroupByUser($user->getId());

        $json = $this->get('serializer')->serialize(
            $groups,
            'json',
            SerializationContext::create()->setGroups(array('id', 'admin-group-list'))
        );
        return new Response($json);
    }

    /** Create a user
     * @param Request $request
     * @Security("is_granted('ROLE_ADMIN')")
     * @return Response
     */
    public function postAction(Request $request)
    {
        $data = $request->request->all();

        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->submit($data);

        // Validate
        /** @var ValidatorService $validatorService */
        $validatorService = $this->get('app.service.validator');
        $errors = $validatorService->validate($user);
        if (count($errors) > 0) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => $errors
            )));
        }
        $password = $this->createPassword();
        /** @var CryptService $cryptor */
        $cryptor = $this->container->get('app.services.cryptor');
        $encryptedPassword = $cryptor->crypt($password);
        $user->setPassword($encryptedPassword);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        try {
            $this->sendRegistrationEmail($user, $password);
        } catch (\Exception $exception) {

        }

        $response = [
            'success' => true,
            'user' => $user
        ];

        $json = $this->get('serializer')->serialize(
            $response,
            'json',
            SerializationContext::create()->setGroups(array('id', 'admin-user-edit'))
        );

        return new Response($json);
    }

    /**
     * @return string
     */
    private function createPassword()
    {
        $alphabet = self::lower . self::upper . self::numbers . self::symbols;
        $password = array(); //remember to declare $password as an array
        // at least one lower case, one upper case, one number and one symbol
        // lower case
        $n = rand(0, strlen(self::lower) - 1);
        $password[] = self::lower[$n];
        // upper case
        $n = rand(0, strlen(self::upper) - 1);
        $password[] = self::upper[$n];
        // number
        $n = rand(0, strlen(self::numbers) - 1);
        $password[] = self::numbers[$n];
        // symbol
        $n = rand(0, strlen(self::symbols) - 1);
        $password[] = self::symbols[$n];
        // store alphabet length
        $alphaLength = strlen($alphabet) - 1;
        $passwordLength = rand(4, 8);
        for ($i = 0; $i < $passwordLength; $i++) {
            $n = rand(0, $alphaLength);
            $password[] = $alphabet[$n];
        }
        shuffle($password); // shuffle password
        return implode($password); //turn the array into a string
    }

    /**
     * @param User $user
     * @param $password
     */
    private function sendRegistrationEmail(User $user, $password)
    {
        $this->get('app.service.mail_service')->send(
            $user,
            'Emails/registration.html.twig',
            'Peeramid - Inscription',
            array(
                'name' => $user->getFirstName() . ' ' . $user->getLastName(),
                'username' => $user->getUsername(),
                'password' => $password,
                'address' => $this->getParameter('front_address')
            )
        );
    }

    /** Delete a user
     * @param integer $id
     * @Security("is_granted('ROLE_ADMIN')")
     * @return Response
     */
    public function deleteAction($id)
    {
        $user = $this->getDoctrine()->getRepository('AppBundle:User')->find($id);

        if ($user === null) {
            throw new NotFoundHttpException('Cet utilisateur n\'existe pas.');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();

        return new Response(json_encode(array('success' => true)));
    }

    /**
     * Update general information of a user
     * @param Request $request
     * @Security("is_granted('ROLE_ADMIN')")
     * @return Response
     */
    public function putAction(Request $request)
    {
        // Get params
        $data = $request->request->all();

        // Load existing user
        $em = $this->getDoctrine()->getManager();
        /** @var User $user */
        $user = $em->getRepository('AppBundle:User')->find($data["id"]);

        if ($user === null) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Cet utilisateur n\'existe pas'
                )
            )));
        }

        $form = $this->createForm(UserType::class, $user);
        $form->submit($data);

        // Validate
        /** @var ValidatorService $validatorService */
        $validatorService = $this->get('app.service.validator');
        $errors = $validatorService->validate($user);
        if (count($errors) > 0) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => $errors
            )));
        }
        if (isset($data['password'])) {
            if (!$this->checkPasswordStrength($data['password'])) {
                return new Response(json_encode(array(
                    'success' => false,
                    'errors' => array(
                        'field' => 'password',
                        'value' => $data['password'],
                        'message' => 'Le mot de passe n\'est pas assez fort.'
                    )
                )));
            }
            // Define user password
            $this->defineUserPassword($user, $data['password']);
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $response = [
            'success' => true,
            'user' => $user
        ];

        $json = $this->get('serializer')->serialize(
            $response,
            'json',
            SerializationContext::create()->setGroups(array('id', 'admin-user-edit'))
        );

        return new Response($json);
    }

    /**
     * @param string $password
     * @return bool
     */
    private function checkPasswordStrength(string $password)
    {
        $hasLower = false;
        $hasUpper = false;
        $hasNumber = false;
        $hasSymbol = false;
        $length = strlen($password);
        for ($i = 0; $i < $length; $i++) {
            if (strpos(self::lower, $password[$i]) !== false) {
                $hasLower = true;
            }
            if (strpos(self::upper, $password[$i]) !== false) {
                $hasUpper = true;
            }
            if (strpos(self::numbers, $password[$i]) !== false) {
                $hasNumber = true;
            }
            if (strpos(self::symbols, $password[$i]) !== false) {
                $hasSymbol = true;
            }
        }
        return $hasLower && $hasUpper && $hasNumber && $hasSymbol && $length > 7;
    }

    /**
     * Set password if it has changed
     * @param User $user
     * @param $password
     */
    private function defineUserPassword(User $user, $password)
    {
        // Get cryptor service
        /** @var CryptService $cryptor */
        $cryptor = $this->container->get('app.services.cryptor');

        if ($user->getPassword() !== $password) {
            $encryptedPassword = $cryptor->crypt($password);
            $user->setPassword($encryptedPassword);
        }
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function putProfileAction(Request $request)
    {
        // Get params
        $data = $request->request->all();
        // Load existing user
        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $em->getRepository('AppBundle:User')->find($data["id"]);

        if ($user === null) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Cet utilisateur n\'existe pas'
                )
            )));
        }

        $this->get('app.service.access_service')->tryEntity(
            $this->getUser(),
            array($user)
        );

        if (isset($data['password'])) {
            if (!$this->checkPasswordStrength($data['password'])) {
                return new Response(json_encode(array(
                    'success' => false,
                    'errors' => array(
                        'field' => 'password',
                        'value' => $data['password'],
                        'message' => 'Le mot de passe n\'est pas assez fort.'
                    )
                )));
            }
            // Define user password
            $this->defineUserPassword($user, $data['password']);
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $response = [
            'success' => true,
            'user' => $user
        ];

        $json = $this->get('serializer')->serialize(
            $response,
            'json',
            SerializationContext::create()->setGroups(array('id', 'user-light'))
        );

        return new Response($json);
    }

    /**
     * @param integer $id
     * @param Request $request
     * @return Response
     */
    public function postImageAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var User $user */
        $user = $em->getRepository('AppBundle:User')->find($id);

        /** @var User $loggedUser */
        $loggedUser = $this->getUser();

        if ($loggedUser->getRole()->getId() !== Constants::ROLE_ADMIN) {
            $this->get('app.service.access_service')->tryEntity($loggedUser, array($user));
        }

        if ($user == null) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Cet utilisateur n\'existe pas.'
                )
            )));
        }

        /** @var UploadedFile $file */
        $file = $request->files->get('file');

        if ($file == null) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Veuillez envoyer un fichier.'
                )
            )));
        }

        /** @var SettingRepository $settingRepository */
        $settingRepository = $this->getDoctrine()->getRepository('AppBundle:Setting');
        /** @var Setting $maxSizeSetting */
        $maxSizeSetting = $settingRepository->find(Constants::UPLOAD_MAX_SIZE);
        $maxSize = $maxSizeSetting->getValue();

        $result = $this->container->get('app.services.upload')->checkFile($file, $maxSize, null);

        if (!$result['success']) {
            return new Response(json_encode($result));
        }

        $user->setImageFile($file);

        // Validate
        /** @var ValidatorService $validatorService */
        $validatorService = $this->get('app.service.validator');
        $errors = $validatorService->validate($user);
        if (count($errors) > 0) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => $errors
            )));
        }

        $uploadDirectory = $this->getParameter('upload.directory');
        $uploadedFilePattern = sprintf(Constants::USER_FILE_PATH_FORMAT, $user->getId());
        $targetDirectory = $uploadDirectory . $uploadedFilePattern;
        if ($user->getImage() != null) {
            // Delete current image
            $fileName = $targetDirectory . $user->getImage();
            if (file_exists($fileName)) {
                unlink($fileName);
            }
            $user->setImage(null);
        }
        $fs = new Filesystem();
        $fs->mkdir($targetDirectory);
        try {
            /** @var File $imageFile */
            $imageFile = $file->move($targetDirectory, $file->getClientOriginalName());
            unset($file);
        } catch (\Exception $e) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, 'Une erreur est survenue.', $e);
        }
        $user->setImage($imageFile->getFilename());

        $em->flush();

        $response = array(
            'success' => true,
            'user' => $user
        );

        $json = $this->get('serializer')->serialize(
            $response,
            'json',
            SerializationContext::create()->setGroups(array('id', 'admin-user-edit'))
        );
        return new Response($json);
    }

    /**
     * @param int $id
     * @return Response
     */
    public function deleteImageAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var User $user */
        $user = $em->getRepository('AppBundle:User')->find($id);

        if ($user == null) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Cet utilisateur n\'existe pas.'
                )
            )));
        }

        /** @var User $loggedUser */
        $loggedUser = $this->getUser();

        if ($loggedUser->getRole()->getId() !== Constants::ROLE_ADMIN) {
            $this->get('app.service.access_service')->tryEntity($loggedUser, array($user));
        }

        // Delete file
        $uploadDirectory = $this->getParameter('upload.directory');
        $uploadedFilePattern = sprintf(Constants::USER_FILE_PATH_FORMAT, $user->getId());
        $targetDirectory = $uploadDirectory . $uploadedFilePattern;
        $fileName = $targetDirectory . $user->getImage();
        if (file_exists($fileName)) {
            unlink($fileName);
        }

        $user->setImage(null);

        $em->flush();

        $response = array(
            'success' => true,
            'user' => $user
        );

        $json = $this->get('serializer')->serialize(
            $response,
            'json',
            SerializationContext::create()->setGroups(array('id', 'admin-user-edit'))
        );
        return new Response($json);
    }

    /**
     * @param Request $request
     * @return Response
     * @Security("is_granted('ROLE_ADMIN')")
     * @Post("/users/list")
     */
    public function postListAction(Request $request)
    {
        $createdUsers = array();
        $errorUsers = array();
        $file = $request->files->get('file');

        if ($file == null) {
            return new Response(json_encode(array(
                'success' => 'false',
                'errors' => array(
                    'message' => 'Veuillez envoyer un fichier.'
                )
            )));
        }

        /** @var SettingRepository $settingRepository */
        $settingRepository = $this->getDoctrine()->getRepository('AppBundle:Setting');
        /** @var Setting $maxSizeSetting */
        $maxSizeSetting = $settingRepository->find(Constants::UPLOAD_MAX_SIZE);
        $maxSize = $maxSizeSetting->getValue();

        /** @var FileTypeRepository $fileTypeRepository */
        $fileTypeRepository = $this->getDoctrine()->getRepository('AppBundle:FileType');
        $fileTypes = $fileTypeRepository->findCsvType();

        $result = $this->get('app.services.upload')->checkFile($file, $maxSize, $fileTypes);

        if (!$result['success']) {
            return new Response(json_encode($result));
        }

        $targetDirectory = $this->getParameter('upload.directory') . '/temp/';
        $fs = new Filesystem();
        $fs->mkdir($targetDirectory);
        $file->move($targetDirectory, 'temp.csv');
        $em = $this->getDoctrine()->getManager();
        if (($handle = fopen($targetDirectory . 'temp.csv', "r")) !== FALSE) {
            //readfile($targetDirectory . 'temp.csv');
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                if (count($data) === 3) {
                    $temp = array(
                        'email' => $data['0'],
                        'last_name' => $data['1'],
                        'first_name' => $data['2'],
                        'role' => 2
                    );
                    $user = new User();
                    $form = $this->createForm(UserType::class, $user);
                    $form->submit($temp);

                    $username = mb_strtolower(trim($user->getFirstName() . '.' . $user->getLastName()));

                    /** @var FormatService $formatService */
                    $formatService = $this->get('app.service.format_service');
                    $username = normalizer_normalize($username);
                    $username = $formatService->strflat($username);
                    $tempUsername = $username;
                    $i = 1;
                    /** @var User $existingUser */
                    $existingUser = $em->getRepository('AppBundle:User')->findOneByUsername($tempUsername);
                    while ($existingUser != null) {
                        $tempUsername = $username . $i++;
                        $existingUser = $em->getRepository('AppBundle:User')->findOneByUsername($tempUsername);
                    }

                    $user->setUsername($tempUsername);

                    // Validate
                    $validator = $this->get('validator');
                    $listErrors = $validator->validate($user);
                    if (count($listErrors) > 0) {
                        $errors = array();
                        foreach ($listErrors as $error) {
                            /** @var ConstraintViolationInterface $error */
                            $errors[] = array(
                                'field' => $error->getPropertyPath(),
                                'value' => $error->getInvalidValue(),
                                'message' => $error->getMessage()
                            );
                        }
                        $errorUsers[] = array(
                            'user' => $user,
                            'errors' => $errors
                        );
                    } else {
                        $password = $this->createPassword();
                        /** @var CryptService $cryptor */
                        $cryptor = $this->container->get('app.services.cryptor');
                        $encryptedPassword = $cryptor->crypt($password);
                        $user->setPassword($encryptedPassword);

                        $em->persist($user);
                        $em->flush();

                        try {
                            $this->sendRegistrationEmail($user, $password);
                        } catch (\Exception $exception) {

                        }

                        $createdUsers[] = $user;
                    }
                }
            }
            fclose($handle);
        } else {
            return new Response(json_encode(array('success' => 'false')));
        }
        unlink($targetDirectory . 'temp.csv');
        $response = [
            'success' => true,
            'created_users' => $createdUsers,
            'error_users' => $errorUsers
        ];
        $json = $this->get('serializer')->serialize(
            $response,
            'json',
            SerializationContext::create()->setGroups(array('id', 'user-light'))
        );

        return new Response($json);
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Post("/users/request-password")
     */
    public function requestPasswordResetAction(Request $request)
    {
        // Get params
        $data = $request->request->all();

        // Load existing user
        $em = $this->getDoctrine()->getManager();
        /** @var UserRepository $userRepository */
        $userRepository = $em->getRepository('AppBundle:User');
        $users = $userRepository->findByUsernameOrEmail($data["username"]);


        if ($users === null || count($users) == 0) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Cet utilisateur n\'existe pas'
                )
            )));
        }

        /** @var User $user */
        $user = $users[0];

        /** @var PasswordTokenRepository $passwordTokenRepository */
        $passwordTokenRepository = $em->getRepository('AppBundle:PasswordToken');
        $tokens = $passwordTokenRepository->findByUser($user);
        if ($tokens != null && count($tokens) > 0) {
            /** @var PasswordToken $token */
            foreach ($tokens as $token) {
                $em->remove($token);
            }
        }
        $em->flush();

        do {
            $passwordToken = new PasswordToken($user);
            // check if token is unique
            $tokens = $passwordTokenRepository->findByToken($passwordToken->getToken());
        } while ($tokens != null && count($tokens) > 0);

        $em->persist($passwordToken);

        $em->flush();

        $this->sendPasswordEmail($user, $passwordToken);

        return new Response(json_encode(array(
            'success' => true,
            'email' => $user->getEmail()
        )));
    }

    /**
     * @param User $user
     * @param PasswordToken $passwordToken
     */
    private function sendPasswordEmail(User $user, PasswordToken $passwordToken)
    {
        $frontAddress = $this->container->getParameter('front_address');
        if (substr($frontAddress, -1) !== '/') {
            $frontAddress .= '/';
        }
        $this->get('app.service.mail_service')->send(
            $user,
            'Emails/request_reset.html.twig',
            'Peeramid - Demande de réinitialisation du mot de passe',
            array(
                'name' => $user->getFirstName() . ' ' . $user->getLastName(),
                'address' => $frontAddress . 'login/reset/' . $passwordToken->getToken()
            )
        );
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Post("/users/reset-password")
     */
    public function resetPasswordAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        // Get params
        $data = $request->request->all();

        if (!isset($data['token'])) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Token invalide'
                )
            )));
        }

        /** @var PasswordTokenRepository $passwordTokenRepository */
        $passwordTokenRepository = $em->getRepository('AppBundle:PasswordToken');
        /** @var PasswordToken $passwordToken */
        $passwordToken = $passwordTokenRepository->findOneByToken($data['token']);

        if ($passwordToken == null) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Token invalide'
                )
            )));
        }
        $now = new \DateTime();

        if ($now->getTimestamp() - $passwordToken->getDateCreation()->getTimestamp() > 3600) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Token expiré'
                )
            )));
        }

        if (!isset($data['password'])) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Veuillez envoyer un nouveau mot de passe'
                )
            )));
        }

        /** @var CryptService $cryptor */
        $cryptor = $this->container->get('app.services.cryptor');
        $encryptedPassword = $cryptor->crypt($data['password']);

        $passwordToken->getUser()->setPassword($encryptedPassword);

        $em->remove($passwordToken);

        $em->flush();

        $this->sendPasswordResetEmail($passwordToken->getUser());

        return new Response(json_encode(array('success' => true)));
    }

    /**
     * @param User $user
     */
    private function sendPasswordResetEmail(User $user)
    {
        $this->get('app.service.mail_service')->send(
            $user,
            'Emails/reset.html.twig',
            'Peeramid - Mot de passe réinitialisé',
            array(
                'name' => $user->getFirstName() . ' ' . $user->getLastName()
            )
        );
    }
}