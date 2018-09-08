<?php
/**
 *  This file is a part of SmallKeyring
 *  Copyright 2018 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

namespace App\Security;


/**
 * Class AskPassphraseForm
 * @package App\Security
 */
class AskPassphraseForm
{
    /** @var string */
    protected $passphrase;

    /**
     * Get passphrase
     * @return string
     */
    public function getPassphrase(): ?string
    {
        return $this->passphrase;
    }

    /**
     * Set passphrase
     * @param string $passphrase
     * @return AskPassphraseForm
     */
    public function setPassphrase(string $passphrase): AskPassphraseForm
    {
        $this->passphrase = $passphrase;

        return $this;
    }
}