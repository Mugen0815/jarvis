<?php
namespace Mugen0815\OpenAIAssistant;

if(interface_exists('Mugen0815\OpenAIAssistant\SkillsInterface')) return;
/**
 * Interface for defining skills.
 */
interface SkillsInterface
{
    public function getFunctions(): array;
    public function getFncJson(string $sFncName): string;
}
