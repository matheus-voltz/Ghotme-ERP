<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Agents\Contracts\AgentInterface;
use Illuminate\Support\Facades\Log;
use Exception;

class ExecuteBusinessAgent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $agentClass;
    public array $context;

    /**
     * Create a new job instance.
     *
     * @param string $agentClass The fully qualified class name of the Agent to run.
     * @param array $context Data payload required by the agent.
     */
    public function __construct(string $agentClass, array $context)
    {
        $this->agentClass = $agentClass;
        $this->context = $context;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("ExecuteBusinessAgent Job Started for Agent: {$this->agentClass}");

        if (!class_exists($this->agentClass)) {
            Log::error("ExecuteBusinessAgent Failed: Class {$this->agentClass} does not exist.");
            return;
        }

        try {
            // Instantiate the AI Agent
            $agent = app($this->agentClass);

            if (!$agent instanceof AgentInterface) {
                Log::error("ExecuteBusinessAgent Failed: Class {$this->agentClass} must implement AgentInterface.");
                return;
            }

            // Set the context for reference in act()
            $agent->setContext($this->context);

            // 1. Analyze the context
            Log::info("Agent ({$this->agentClass}) analyzing context...");
            $analysisResult = $agent->analyze($this->context);

            if ($analysisResult === null) {
                Log::warning("Agent ({$this->agentClass}) returned null. Aborting action.");
                return;
            }

            // 2. Act based on the analysis
            Log::info("Agent ({$this->agentClass}) acting on analysis...");
            $actionStatus = $agent->act($analysisResult);

            Log::info("ExecuteBusinessAgent Job Finished. Action status: " . ($actionStatus ? 'Success' : 'Failed'));
        } catch (Exception $e) {
            Log::error("ExecuteBusinessAgent encountered an exception: " . $e->getMessage());
            // Depending on the logic, we might want to let the exception bubble up to retry the job.
            // throw $e; 
        }
    }
}
