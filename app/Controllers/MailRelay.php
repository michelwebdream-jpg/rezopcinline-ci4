<?php

namespace App\Controllers;

use CodeIgniter\Email\Email;

class MailRelay extends BaseController
{
    protected Email $email;

    public function __construct()
    {
        $this->email = \Config\Services::email();
    }

    public function sendLegacy(): string
    {
        if ($this->request->getMethod() !== 'POST') {
            return 'return_txt=-1';
        }

        $to = trim((string) $this->request->getPost('destinataire'));
        $subject = trim((string) $this->request->getPost('titre'));
        $content = (string) $this->request->getPost('contenu');

        if ($to === '' || $subject === '' || $content === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return 'return_txt=-1';
        }

        // Limit header injection risk through subject.
        $subject = str_replace(["\r", "\n"], ' ', $subject);

        $this->email->setFrom('info@web-dream.fr', 'REZO+ PC Inline - Web-Dream');
        $this->email->setTo($to);
        $this->email->setSubject($subject);
        $this->email->setMailType('html');
        $this->email->setMessage($content);

        if ($this->email->send()) {
            return 'return_txt=1';
        }

        log_message('error', 'MailRelay sendLegacy failed: ' . $this->email->printDebugger(['headers', 'subject']));
        return 'return_txt=-1';
    }
}

