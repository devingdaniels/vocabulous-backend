<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Word;
use App\Models\Deck;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class WordController extends Controller
{
    public function create(Request $request)
    {
        // Validate the request parameters
        $validated = $request->validate([
            'word' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id', // User ID must exist in the database
            'deck_id' => 'required|exists:decks,id', // Deck ID must exist in the database
        ]);

        // Check if the word already exists in the database
        $word = Word::where('word', $validated['word'])->first();

        if (!$word) {
            // Word doesn't exist, fetch its data from OpenAI
            $wordData = $this->fetchWordFromOpenAI($validated['word']);
dd($wordData);
            if (!$wordData) {
                // If OpenAI fails to return valid data
                return response()->json([
                    'error' => 'Unable to fetch word data from OpenAI.',
                ], 500);
            }

            // Create a new word record in the database
            $word = Word::create($wordData);
        }

        // Associate the word with the deck without duplicating associations
        $deck = Deck::find($validated['deck_id']);
        $deck->words()->syncWithoutDetaching([$word->id]);

        // Return a success response with the word details
        return response()->json([
            'message' => 'Word processed successfully.',
            'word' => $word,
        ], 200);
    }

    /**
     * Fetch word data from OpenAI using the provided word.
     *
     * @param string $word
     * @return array|null
     */
    private function fetchWordFromOpenAI($word)
    {
        $apiKey = config('services.openapi.api_key'); // Fetch the OpenAI API key from .env
        $prompt = $this->createPrompt($word);
        // Make the HTTP request to OpenAI
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',

        ])->post(config('services.openapi.api_url'), [
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'system', 'content' => $prompt],
            ],
        ]);
        dd($response);
        // Check if the request to OpenAI succeeded
        if ($response->failed()) {
            return null;
        }

        $choices = $response->json('choices');
        if (empty($choices) || empty($choices[0]['message']['content'])) {
            return null;
        }

        // Parse and return the JSON content from OpenAI's response
        return json_decode($choices[0]['message']['content'], true);
    }

    /**
     * Generate the OpenAI prompt for word details.
     *
     * @param string $word
     * @return string
     */
    private function createPrompt($word)
    {
        return <<<EOT
Construct a detailed JSON object for the English word "{$word}". Ensure the definition is clear and concise.
If "{$word}" is a verb, include its conjugations in the present, past, future, and present continuous tenses.
For non-verbs, the conjugations attribute should be an empty object {}. Use complete sentences for examples,
starting with a capital letter and ending with a period. Follow the example format provided very closely.

Example Format:
{
  "word": "make",
  "definition": "To create, produce, or construct something",
  "wordType": "Verb",
  "exampleSentence": "I make dinner every evening.",
  "phoneticSpelling": "meyk",
  "isIrregular": true,
  "pastParticiple": "made",
  "conjugations": {
    "present": {
      "I": "make",
      "you": "make",
      "he/she/it": "makes",
      "we": "make",
      "they": "make"
    },
    "past": {
      "I": "made",
      "you": "made",
      "he/she/it": "made",
      "we": "made",
      "they": "made"
    },
    "future": {
      "I": "will make",
      "you": "will make",
      "he/she/it": "will make",
      "we": "will make",
      "they": "will make"
    },
    "presentContinuous": {
      "I": "am making",
      "you": "are making",
      "he/she/it": "is making",
      "we": "are making",
      "they": "are making"
    }
  }
}

For the given word "{$word}", fill in the following:
{
  "word": "{$word}",
  "definition": "[Enter the definition]",
  "wordType": "[Enter 'Noun', 'Verb', 'Adjective', etc.]",
  "exampleSentence": "[Enter an example sentence using '{$word}']",
  "phoneticSpelling": "[Provide the IPA phonetic spelling]",
  "isIrregular": [true/false],
  "pastParticiple": "[Enter past participle if verb, empty string if not]",
  "conjugations": [Enter conjugations as detailed in the format above or '{}' if not applicable]
}
EOT;
    }
}
