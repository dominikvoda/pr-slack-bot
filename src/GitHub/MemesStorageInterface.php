<?php declare(strict_types = 1);

namespace BE\PRSlackBot\GitHub;

interface MemesStorageInterface
{
    public function getMergedMeme(): string;
}
