<?php

namespace Biblioteca\TypesenseBundle\Query;

enum InfixEnum: string
{
    case OFF = 'off';
    case ALWAYS = 'always';
    case FALLBACK = 'fallback';
}
