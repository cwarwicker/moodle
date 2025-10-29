<?php
namespace mod_assign\markingagreement;
use mod_assign\hook\marking_agreement_get_names;

abstract class method {

    /**
     * Return the display name of the marking agreement method.
     * @return string
     */
    abstract public static function get_name(): string;

    /**
     * Inject the marking_agreement_get_names hook and return the names it retrieves from watchers
     * @return array
     */
    public static function inject_hook_get_names(): array {
        $hook = new \mod_assign\hook\marking_agreement_get_names();
        \core\di::get(\core\hook\manager::class)->dispatch($hook);
        return $hook->names;
    }

    /**
     * Respond to the marking_agreement_get_names hook with the class and display name of this method
     * @param marking_agreement_get_names $hook
     * @return void
     */
    public static function respond_to_get_names(marking_agreement_get_names $hook) {
        $hook->add(static::get_name(), static::class);
    }

    public static function should_respond(assignment $assignment) {
        // Todo: lookup assignment marking agreement method setting
        // Example: assume it's "max"
        $setting = 'mod_assign\\markingagreement\\max';
        return (self::class === $setting);
    }
}