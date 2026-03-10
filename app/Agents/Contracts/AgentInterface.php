<?php

namespace App\Agents\Contracts;

interface AgentInterface
{
    /**
     * Define the System Prompt or "Role" for the AI Agent.
     */
    public function getSystemPrompt(): string;

    /**
     * Analyze the provided context and return the AI's response or decision.
     * 
     * @param array $context The data or situation the agent needs to analyze.
     * @return mixed The analysis result (string, JSON, array, etc).
     */
    public function analyze(array $context);

    /**
     * Store the context for later reference during action.
     */
    public function setContext(array $context): void;

    /**
     * Execute any action resulting from the analysis (Optional implementation).
     * 
     * @param mixed $analysisResult The result returned from the analyze method.
     * @return bool status of the action
     */
    public function act($analysisResult): bool;
}
