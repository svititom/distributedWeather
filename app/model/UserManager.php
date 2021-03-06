<?php

namespace App\Model;

use App\Entities\Device;
use App\Entities\User;
use Exception;
use Instante\Helpers\MissingValueException;
use Kdyby\Doctrine\EntityManager;
use Latte\Engine;
use Nette;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;
use Nette\Mail\SmtpMailer;
use Nette\Security\Passwords;


/**
 * Users management.
 */
class UserManager implements Nette\Security\IAuthenticator
{
    use Nette\SmartObject;

    /**
     * @param $username or email
     *
     * @return null|User
     */
    public function findUserByUsername($username)
    {
        if (!$username){
            return null;
        }
        $user = $this->em->getRepository(User::class)->findOneBy(array('email' => $username));
        if (!$user){
            $user = $this->em->getRepository(User::class)->findOneBy(array('name'=> $username));
        }
        return $user;
    }

    public function findUserById($id)
    {
        return $this->em->getRepository(User::class)->findOneBy(["id" => $id]);
    }

    /**
     *
     * @var \Kdyby\Doctrine\EntityManager
     *
     */
    public $em;

    /**
     * @var UserMailer
     */
    private  $userMailer;



    public function __construct(UserMailer $userMailer, \Kdyby\Doctrine\EntityManager $em)
    {
        $this->userMailer = $userMailer;
        $this->em = $em;
    }


    /**
     * Performs an authentication.
     * @return Nette\Security\Identity
     * @throws Nette\Security\AuthenticationException
     */
    public function authenticate(array $credentials)
    {
        list($username, $password) = $credentials;


        $user = $this->findUserByUsername($username);

        if (!$user) {
            throw new Nette\Security\AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);

        } elseif (!$user->authenticate($password)) {
            throw new Nette\Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);

        }
        //flush in case password was rehashed
        $this->em->flush($user);

        //$arr = $row->toArray();
        //unset($arr[self::COLUMN_PASSWORD_HASH]);
        //todo create a new identity object
        return new Nette\Security\Identity($user->getId(), $user->getRole(), NULL);

    }


    /**
     * @param $email users email or username
     * @param $hash - verification hash in db
     * @param $password - new password
     */
    public function resetPassword($email, $hash, $password) {
        $user = $this->findUserByUsername($email);
        $user->resetPassword($hash, $password);
    }

    public function sendResetMail($email){
        //todo implement
    }


    /**
     * @param $username
     * @param $email
     * @param $password
     * @throws DuplicateNameException
     * @throws MailingException
     */
    public function addUser($username, $email, $password)
    {
        if(!$email){
            throw new \InvalidArgumentException();
        }
        if($this->findUserByUsername($username)){
            throw new DuplicateNameException();
        }

        $user = new User($username, $email, $password, User::ROLE_USER);
        $this->em->persist($user);
        $this->em->flush($user);
        try {
            $this->userMailer->sendVerificationMail($user->generateVerificationHash(), $email);
        }catch (Exception $e) {
            throw new MailingException();
        }
    }


    /**
     * @param $email or username
     * @param $hash
     * @return bool
     * @throws UserNotFoundException
     */
    public function verifyUser($email, $hash){
        $user = $this->findUserByUsername($email);
        if(!$user){
            throw new UserNotFoundException();
        }
        return $user->verify($hash);
    }

    /**
     * @param $id
     * @param $deviceName
     * @return bool
     */
    public function userHasDevice($id, $deviceName): bool{
        $device = $this->em->getRepository(Device::class)->findBy(["name" => $deviceName, "id" => $id]);
        return ($device != null);
    }

    /**
     * @param $id
     * @param Device $device
     * @throws DuplicateNameException
     */
    public function addDevice($id, Device $device) {
        if ($this->userHasDevice($id, $device->getName())){
            throw new DuplicateNameException();
        }
        $user = $this->findUserById($id);
        $user->addDevice($device);
        $this->em->flush($user);
    }

    public function getUserDevices(string $userId)
    {
        $user = $this->findUserById($userId);
        return $user->getDevices();
    }

}


class DuplicateNameException extends \Exception
{}
class DuplicateEmailException extends \Exception
{}
class ExpiredLinkException extends \Exception
{}
class UserNotFoundException extends \Exception
{}
class MailingException extends \Exception {}