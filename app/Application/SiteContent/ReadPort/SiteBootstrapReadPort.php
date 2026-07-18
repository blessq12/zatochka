<?php

namespace App\Application\SiteContent\ReadPort;

interface SiteBootstrapReadPort
{
    /**
     * @return array{
     *     company: array{
     *         owner_name: string,
     *         inn: string,
     *         ogrn: string,
     *         legal_address: string,
     *         actual_address: string
     *     },
     *     contacts: array{
     *         contact_person: string,
     *         phone: string,
     *         email: string,
     *         address: array{main: string, directions: string},
     *         social: array{email: string, links: list<array{name: string, url: string, icon: ?string}>}
     *     },
     *     schedule: array{days: list<array<string, mixed>>},
     *     prices: list<array{
     *         category: string,
     *         name: string,
     *         price: string,
     *         prefix: ?string,
     *         description?: ?string
     *     }>,
     *     delivery_info: array{
     *         free_conditions: list<string>,
     *         advantages: list<array{title: string, description: string}>
     *     },
     *     faq: array{items: list<array{id: int, question: string, answer_lines: list<string>}>}
     * }
     */
    public function getBootstrap(): array;
}
