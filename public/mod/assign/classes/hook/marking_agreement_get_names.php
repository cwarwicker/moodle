<?php
namespace mod_assign\hook;

final class marking_agreement_get_names {
    public array $names = [];
    public function add(string $name, string $class) {
        $this->names[$class] = $name;
    }
}