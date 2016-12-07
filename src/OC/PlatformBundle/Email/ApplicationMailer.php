<?php

// src/OC/PlatformBundle/Email/ApplicationMailer.php

namespace OC\PlatformBundle\Email;

use OC\PlatformBundle\Entity\Application;

class ApplicationMailer {

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    public function __construct(\Swift_Mailer $mailer) {
        $this->mailer = $mailer;
    }

    public function sendNewNotification(Application $application) {
        $message = new \Swift_Message(
                'Nouvelle candidature', 'Vous avez reçu une nouvelle candidature.'
        );

        //$email = "texte";
        $email = "ddchamot@hotmail.com";
        //$email = $application->getAdvert()->getAuthor();
        
        try {
            $message->addTo($email);
        } catch (\Swift_RfcComplianceException $e) {
            echo "Address " . $email . " seems invalid : " . $e->getMessage();
        }

        $emailfrom = 'admin@votresite.com';
        try {
            $message->addFrom($emailfrom);
        } catch (\Swift_RfcComplianceException $e) {
            echo "Address FROM " . $emailfrom . " seems invalid : " . $e->getMessage();
        }

        try {
            $this->mailer->send($message);
        } catch (\Swift_TransportException $e) {
            echo "Error while sending : " . $e->getMessage();
        }
    }

}
