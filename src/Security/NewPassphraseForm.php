<?php
/**
 *  This file is a part of SmallKeyring
 *  Copyright 2018 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

namespace App\Security;

/**
 * Class NewPassphraseForm
 * @package App\Security
 */
class NewPassphraseForm
{
    /** @var string */
    protected $passphrase;
    /** @var string */
    protected $passphraseConfirm;

    /**
     * Get passphrase
     * @return string
     */
    public function getPassphrase(): ?string
    {
        return $this->passphrase;
    }

    /**
     * Get passphrase confirmation
     * @return string
     */
    public function getPassphraseConfirm(): ?string
    {
        return $this->passphraseConfirm;
    }

    /**
     * Set passphrase
     * @param string $passphrase
     * @return NewPassphraseForm
     */
    public function setPassphrase(string $passphrase): NewPassphraseForm
    {
        $this->passphrase = $passphrase;

        return $this;
    }

    /**
     * Set passphrase confirmation
     * @param string $passphraseConfirm
     * @return NewPassphraseForm
     */
    public function setPassphraseConfirm(string $passphraseConfirm): NewPassphraseForm
    {
        $this->passphraseConfirm = $passphraseConfirm;

        return $this;
    }

}