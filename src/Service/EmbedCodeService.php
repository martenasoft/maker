<?php

namespace MartenaSoft\Maker\Service;


class EmbedCodeService
{
    private string $content;
    private array $contentArray = [];
    private array $data;

    public function setContent(string $content): self
    {
        $this->content = $content;
        $this->contentArray = explode("\n", $this->content);
        return $this;
    }

    public function getResult(): string
    {
        $result = $this->content;
        return $result;
    }

    public function findMethod(string $name): self
    {
        $file = explode("\n", $this->content);
        $data = [];
        $body = '';

        foreach ($file as $lineNumber => $line) {
            $pattern = "/((public|protected|private)\s+function\s+|function\s+)($name)/";

            if (preg_match($pattern, $line, $matches) && isset($matches[2]) && isset($matches[3])) {
                //  if (!empty())
               $data['methods'][$matches[3]] = [
                    'line' => $lineNumber + 1,
                    'accessModifier' => $matches[2],
                    "name" => $matches[3]
                ];
            }


            if (empty($data['methods'][$name]['arguments']['open_quote']) && strpos($line, "(") !== false) {
                $data['methods'][$name]['arguments'] = [
                    'open_quote' => [
                        'line' => $lineNumber + 1
                    ]
                ];
            }


            if (!empty($data['methods'][$name]['arguments']['open_quote']) &&
                empty($data['methods'][$name]['arguments']['close_quote'])) {

                if (strpos($line, ")") !== false) {
                    $data['methods'][$name]['arguments']['close_quote'] = ['line' => $lineNumber];

                    if(empty($data['methods'][$name]['arguments']['variables'])
                        && preg_match("/\((.*)\)/", $line, $matches_) && !empty($matches_[1])) {
                        if (strpos($matches_[1], ',') !== false) {
                            $argumenVariables = explode(',', $matches_[1]);

                            foreach ($argumenVariables as $arg_) {
                                $data['methods'][$name]['arguments']['variables'][] = [
                                    'line' => $lineNumber + 1,
                                    'vars' => $arg_
                                ];
                            }
                        } else {
                            $data['methods'][$name]['arguments']['variables'][] = [
                                'line' => $lineNumber + 1,
                                'vars' => $matches_[1]
                            ];
                        }
                    }

                } elseif (!empty($lineNumber) && !empty(preg_replace('/\s+/', "", $line))) {
                    if (strpos($line, '$') !== false) {
                        $data['methods'][$name]['arguments']['variables'][] = [
                            'line' => $lineNumber + 1,
                            'vars' => $line
                        ];
                    }
                }
            }


            if (!empty($data['methods'][$name]['arguments']['close_quote']) &&
                empty($data['methods'][$name]['body']['close_quote'])) {

                if (strpos($line, "{") != false) {
                    $data['methods'][$name]['body']['open_quote_'][] = [
                        'line' => $lineNumber + 1,
                        'body' => $line
                    ];
                }

                if (isset($data['methods'][$name]['body']['open_quote_'])) {
                    $data['methods'][$name]['body']['lines'][] = [
                        'line' => $lineNumber + 1,
                        'body' => $line
                    ];
                }



                if (strpos($line, "}") != false) {
                    $data['methods'][$name]['body']['close_quote_'][] = [
                        'line' => $lineNumber +  1,
                        'body' => $line
                    ];
                }

                if (isset($data['methods'][$name]['body']['open_quote_']) &&
                    isset($data['methods'][$name]['body']['close_quote_']) &&
                    count($data['methods'][$name]['body']['open_quote_']) ==
                    count($data['methods'][$name]['body']['close_quote_'])

                ) {

                    $data['methods'][$name]['body']['close_quote'] = [
                        'body' => $line,
                        'line' => $lineNumber + 1
                    ];
                }

            }

            if (!empty($vars = $this->getVariable($line, $lineNumber))) {
                $data['methods'][$name]['vars'][] = $vars;
            }
        }

        if (!empty($data['methods'][$name]['arguments']['variables'])) {
            foreach ($data['methods'][$name]['arguments']['variables'] as $i => $args) {

                if (strpos($args['vars'], ",") !== false) {
                    $tmpVal = explode(",", $args['vars']);
                    if (isset($data['methods'][$name]['arguments']['variables'][$i + 1]['vars']) && isset($tmpVal[1])) {
                        $data['methods'][$name]['arguments']['variables'][$i + 1]['vars']
                            = preg_replace( ['/\s+/'], [''], $tmpVal[1]) .
                            ' '.
                            $data['methods'][$name]['arguments']['variables'][$i + 1]['vars'];

                        $data['methods'][$name]['arguments']['variables'][$i]['vars'] = str_replace(
                            $tmpVal[1],
                            '',
                            $data['methods'][$name]['arguments']['variables'][$i]['vars']
                        );

                    }
                }
                $data['methods'][$name]['arguments']['variables'][$i]['vars']
                    = preg_replace(['/^\s+|\s+$/', '/\s{2,}/'], ['', ' '],
                                   $data['methods'][$name]['arguments']['variables'][$i]['vars']);

                if (strpos($data['methods'][$name]['arguments']['variables'][$i]['vars'], ' $') !== false) {
                    list($type, $varName)
                        = explode(' ', $data['methods'][$name]['arguments']['variables'][$i]['vars']);
                    $data['methods'][$name]['arguments']['variables'][$i]['vars'] = [
                        'name' => $name,
                        'type' => $type
                    ];
                } else {
                    $data['methods'][$name]['arguments']['variables'][$i]['vars'] = [
                        'name' => $data['methods'][$name]['arguments']['variables'][$i]['vars'],
                        'type' => ""
                    ];
                }

            }
        }

        $this->data = $data;

        $this->set('TEst', 19);
        dump($data);
        die('test');
        return $this;
    }

    public function set(string $body, int $line, bool $isAppend = false): self
    {
        if (!isset($this->contentArray[$line])) {
            return $this;
        }

        if ($isAppend) {
            array_splice($this->contentArray, $line, null, $body);
        } else {
            $this->contentArray[$line] = $body;
        }
        
        return $this;
    }

    private function getVariable(string $content, int $lineNumber): ?array
    {

        if (preg_match_all('/\$([a-zA-Z0-9_]+)/', $content, $matcher) && !empty($matcher)) {
            if (!isset($return)) {
                $return = [];
            }
            $return[] = [
                'name' => $matcher[0],
                'line' => $lineNumber + 1
            ];

            return $return;
        }

        return null;
    }
}