<?php

namespace Studit\H5PBundle\Service;

class XApiStatementValidator
{
    public function isValid(array $statement): bool
    {
        return $this->validateActor($statement['actor'] ?? [])
            && $this->validateVerb($statement['verb'] ?? [])
            && $this->validateObject($statement['object'] ?? []);
    }

    private function validateActor(array $actor): bool
    {
        $identifiers = ['mbox', 'mbox_sha1sum', 'openid', 'account'];
        foreach ($identifiers as $identifier) {
            if (isset($actor[$identifier])) {
                return true;
            }
        }
        return false;
    }

    private function validateVerb(array $verb): bool
    {
        return isset($verb['id']) && filter_var($verb['id'], FILTER_VALIDATE_URL);
    }

    private function validateObject(array $object): bool
    {
        return isset($object['objectType']) || isset($object['id']);
    }
}