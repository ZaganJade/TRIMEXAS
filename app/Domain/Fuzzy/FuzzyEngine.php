<?php

declare(strict_types=1);

namespace App\Domain\Fuzzy;

/**
 * Facade mesin fuzzy: EligibilityChecker -> Fuzzifier -> InferenceEngine -> Defuzzifier.
 *
 * Output:
 *   - eligible candidate -> FuzzyResult
 *   - ineligible candidate -> EligibilityResult (caller harus cek tipe)
 *
 * Untuk audit trail, caller dapat mengakses:
 *   - $result->memberships
 *   - $result->ruleEvaluations
 */
final class FuzzyEngine
{
    public function __construct(
        private readonly EligibilityChecker $eligibility = new EligibilityChecker(),
        private readonly Fuzzifier $fuzzifier = new Fuzzifier(),
        private readonly InferenceEngine $inference = new InferenceEngine(),
        private readonly Defuzzifier $defuzzifier = new Defuzzifier(),
    ) {
    }

    public function run(CandidateInput $input, EngineSnapshots $snapshots): FuzzyResult|EligibilityResult
    {
        $eligibility = $this->eligibility->check($input);

        if (! $eligibility->eligible) {
            return $eligibility;
        }

        $memberships = $this->fuzzifier->fuzzify($input, $snapshots->fuzzySets);
        $evaluations = $this->inference->execute($memberships, $snapshots->rules, $snapshots->thresholds);
        $score = $this->defuzzifier->weightedAverage($evaluations);
        $category = $score === 0.0
            ? RuleSnapshot::CONSEQUENT_TIDAK_LAYAK
            : $snapshots->thresholds->categorize($score);

        return new FuzzyResult(
            candidateId: $input->candidateId,
            score: $score,
            category: $category,
            memberships: $memberships,
            ruleEvaluations: $evaluations,
        );
    }
}
