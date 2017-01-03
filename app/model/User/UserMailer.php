<?php
/**
 * Created by PhpStorm.
 * User: swith
 * Date: 28-Dec-16
 * Time: 23:37
 */
namespace App\Model;

use Latte\Engine;
use Nette;
use Nette\Mail\Message;
use Nette\Mail\SmtpMailer;

class UserMailer{

	use Nette\SmartObject;




	/**
	 * @param $hash
	 * @param $email
	 */
	public function sendVerificationMail($hash, $email){
		//todo implement mailer
		$latte = new Engine;
		$params = [
			'hash' => $hash,
			'email' => $email,
		];
		$mail = new Message();
		$mail->setFrom('Distributed Weather <distributedweather@gmail.com>')
			->addTo($email)
			->setSubject('Email verification')
			->setHTMLBody($latte->renderToString(__DIR__ . '/templates/emailVerify.latte',$params));
		$mailer = new SmtpMailer([
			'host' => 'smtp.gmail.com',
			'username' => 'distributedweather@gmail.com',
			'password' => 'This is such a secure password :3',
			'secure' => 'ssl',
		]);

		$mailer->send($mail);

	}

	public function sendResetMail($email){

		$hash = md5(rand(0,10000));
		$hashExpiration = date("Y-m-d G:i:s",strtotime('tomorrow'));
		//todo extract into UserManager
		$this->database->table(self::TABLE_NAME)
			->where(self::COLUMN_EMAIL, $email)
			->update([
				self::COLUMN_HASH => $hash,
				self::COLUMN_HASH_EXPIRATION => $hashExpiration,
			]);

		$latte = new Engine;
		$params = [
			'hash' => $hash,
			'hashExpiration' => $hashExpiration,
			'email' => $email,
		];
		$mail = new Message();
		$mail->setFrom('Distributed Weather <distributedweather@gmail.com>')
			->addTo($email)
			->setSubject('Forgotten password')
			->setHTMLBody($latte->renderToString(__DIR__ . '/templates/emailForgot.latte',$params));
		$mailer = new SmtpMailer([
			'host' => 'smtp.gmail.com',
			'username' => 'distributedweather@gmail.com',
			'password' => 'This is such a secure password :3',
			'secure' => 'ssl',
		]);

		$mailer->send($mail);
	}

}