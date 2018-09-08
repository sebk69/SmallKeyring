<?php
/**
 *  This file is a part of SmallKeyring
 *  Copyright 2018 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

namespace App\Security;

/**
 * Class ChangePassphraseForm
 * @package App\Security
 */
class ChangePassphraseForm
{
    /** @var string */
    protected $passphrase;
    /** @var string */
    protected $oldPassphrase;
    /** @var string */
    protected $passphraseConfirmation;

    /**
     * Get passphrase
     * @return null|string
     */
    public function getPassphrase(): ?string
    {
        return $this->passphrase;
    }

    /**
     * Get old passphrase
     * @return null|string
     */
    public function getOldPassphrase(): ?string
    {
        return $this->oldPassphrase;
    }

    /**
     * Get passphrase confirmation
     * @return null|string
     */
    public function getPassphraseConfirmation(): ?string
    {
        return $this->passphraseConfirmation;
    }


    /**
     * Set passphrase
     * @param string $passphrase
     * @return ChangePassphraseForm
     */
    public function setPassphrase(string $passphrase): ChangePassphraseForm
    {
        $this->passphrase = $passphrase;

        return $this;
    }

    /**
     * Set old passphrase
     * @param string $oldPassphrase
     * @return ChangePassphraseForm
     */
    public function setOldPassphrase(string $oldPassphrase): ChangePassphraseForm
    {
        $this->oldPassphrase = $oldPassphrase;

        return $this;
    }

    /**
     * Set passphrase confirmation
     * @param string $passphraseConfirmation
     * @return ChangePassphraseForm
     */
    public function setPassphraseConfirmation(string $passphraseConfirmation): ChangePassphraseForm
    {
        $this->passphraseConfirmation = $passphraseConfirmation;

        return $this;
    }
}