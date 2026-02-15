# 🏗️ Benchmark Run #2: The "Architect & Builder" Pattern

In this second benchmark run, we changed the approach significantly. Instead of asking models to generate the package "zero-shot", we separated the **Planning** and **Execution** phases.

## 🧪 The Experiment Setup

1.  **The Architect (Claude Opus 4.6):**
    *   We asked Claude Opus to create a comprehensive **Implementation Plan**.
    *   The plan included: File structure, TDD steps (Tests first), exact code snippets for configuration, and architectural decisions (Singleton pattern, Facades).
    *   [View the Implementation Plan](./docs/plans/2026-02-15-request-shield.md)

2.  **The Builders (Open Source Models):**
    *   We fed this detailed plan to the Open Source models.
    *   **Task:** "Execute this plan. Write the code exactly as specified, step-by-step."

## 🎯 Hypothesis
> *Can smaller/open-source models produce "Senior Level" code if a proprietary "Smart Model" does the architectural thinking for them?*

---

## 🏎️ Performance & Adherence

| Rank | Builder Model | Time | Plan Adherence | Verdict |
| :--- | :--- | :--- | :--- | :--- |
| 🥇 | **GLM-5** | `3.5s`* | ⭐⭐⭐⭐⭐ | **Incredible Speed & Precision** |
| 🥈 | **Kimi k2.5** | `30.3s` | ⭐⭐⭐⭐⭐ | **Perfect Execution (TDD)** |
| 🥉 | **Devstral 2** | `24.7s` | ⭐⭐ | **Drifted (Hallucinated Features)** |
| 4. | **MiniMax 2.5** | `7m 43s` | ⭐⭐⭐ | **Too Slow & Structural Drift** |

*\*Note: The 3.5s time for GLM-5 is exceptionally fast and might indicate high token throughput or a measurement anomaly, but the output quality is verified.*

---

## 🔍 Detailed Analysis

### 1. GLM-5 (The Speedster)
*   **Result:** GLM-5 followed the plan meticulously. It correctly implemented the `phpunit.xml` config, the Middleware logic, and even the specific Blade view CSS from the plan.
*   **Code Quality:** It respected `readonly` classes and the test structure.
*   **Impact:** This proves that GLM-5 is an excellent "Worker" model. If you give it a blueprint, it builds it faster than any human.

### 2. Kimi k2.5 (The Reliable Engineer)
*   **Result:** Kimi executed the TDD strategy perfectly. It generated the Unit Tests (`ShieldServiceTest`) exactly as requested in the plan before implementing the logic.
*   **Code Quality:** Strict adherence to PHP 8.2 types and the provided namespace structure. It didn't try to "invent" new things, which is exactly what we want in this scenario.

### 3. Devstral 2 (The Creative Rebel)
*   **Problem:** Devstral **hallucinated features** that were not in the plan.
    *   The Plan: Block IPs & User-Agents.
    *   Devstral: Implemented **Rate Limiting** (`max_requests_per_minute`) and request tracking.
*   **Verdict:** While the code works, it failed the specific task of "following the plan". In a real team, this would mean the developer implemented unrequested features, increasing complexity and bugs.

### 4. MiniMax 2.5 (The Slow Builder)
*   **Problem:** It took nearly 8 minutes to execute a plan that was already written.
*   **Drift:** It changed the directory structure slightly (using `src/Support/` instead of following the plan's root `src/` for the Service). It also ignored the specific Testbench setup from the plan in favor of its own Mockery implementation.

---

## 🆚 Comparison: Zero-Shot vs. Plan-Execute

How much did the models improve compared to [Run #1 (Zero-Shot)](../README.md)?

| Model | Zero-Shot Quality | With Opus Plan Quality | Improvement |
| :--- | :--- | :--- | :--- |
| **GLM-5** | Complex, Slow | **Perfect, Instant** | 🚀 **Massive** |
| **Kimi k2.5** | Over-Engineered, Slow | **Focused, Faster** | 🟢 **Significant** |
| **Devstral** | Incomplete | **Feature Creep** | 🔴 **Worse (Disobedient)** |
| **MiniMax** | Good Logic | **Structural Issues** | 🟡 **Neutral** |

---

## 💡 Conclusion

**The "Architect & Builder" pattern is a game changer for Open Source AI.**

1.  **GLM-5 and Kimi k2.5** reached **Claude Opus Level Code Quality** when they were provided with a plan created by Opus.
2.  This allows developers to save money: Use the expensive model (Opus) **once** to generate the plan/architecture, and use cheap/fast models (GLM/Kimi) to write the actual lines of code and tests.
3.  **Warning:** Some models (like Devstral) struggle to stay within the boundaries of a plan and hallucinate extra features, which can be dangerous for project stability.
