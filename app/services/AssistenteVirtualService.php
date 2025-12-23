<?php

class AssistenteVirtualService
{
    private PDO $conn;
    private string $baseUrl;
    private int $assistantUserId;
    private array $knowledgeBase = [];

    public function __construct(PDO $conn, string $baseUrl)
    {
        $this->conn = $conn;
        $this->baseUrl = rtrim($baseUrl, '/') . '/';
        $this->knowledgeBase = $this->loadKnowledgeBase();
        $this->assistantUserId = $this->ensureAssistantUserExists();
    }

    public function getAssistantUserId(): int
    {
        return $this->assistantUserId;
    }

    public function isAssistantUser(?int $userId): bool
    {
        return $userId !== null && $userId === $this->assistantUserId;
    }

    public function buildAutomatedReply(string $question): string
    {
        $question = trim($question);
        if ($question === '') {
            return $this->fallbackResponse();
        }

        $normalizedQuestion = $this->normalizeText($question);
        $bestEntry = null;
        $bestScore = 0;

        foreach ($this->knowledgeBase as $entry) {
            $score = $this->scoreEntry($normalizedQuestion, $entry);
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestEntry = $entry;
            }
        }

        if ($bestEntry && $bestScore > 0) {
            return $this->formatEntry($bestEntry);
        }

        return $this->fallbackResponse();
    }

    public function getAssistantSummary(): array
    {
        return [
            'titulo' => 'Assistente Virtual',
            'descricao' => 'FAQ, onboarding guiado e respostas iniciais para revisão humana.',
        ];
    }

    private function loadKnowledgeBase(): array
    {
        $file = __DIR__ . '/../assistant/knowledge_base.php';
        if (is_file($file)) {
            $entries = require $file;
            if (is_array($entries)) {
                return array_map(function ($entry) {
                    $entry['normalized_keywords'] = array_map(fn($k) => $this->normalizeText($k), $entry['keywords'] ?? []);
                    return $entry;
                }, $entries);
            }
        }
        return [];
    }

    private function ensureAssistantUserExists(): int
    {
        $login = 'assistente.virtual';
        $stmt = $this->conn->prepare("SELECT id_usuario FROM tb_user WHERE login_user = :login LIMIT 1");
        $stmt->bindValue(':login', $login);
        $stmt->execute();

        $existingId = $stmt->fetchColumn();
        if ($existingId) {
            return (int) $existingId;
        }

        $now = date('Y-m-d H:i:s');
        $password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

        $insertSql = "INSERT INTO tb_user(
            usuario_user, login_user, sexo_user, idade_user, email_user, email02_user,
            senha_user, senha_default_user, endereco_user, numero_user, cidade_user, bairro_user,
            estado_user, telefone01_user, telefone02_user, data_create_user, usuario_create_user,
            fk_usuario_user, ativo_user, data_admissao_user, vinculo_user, nivel_user,
            cargo_user, depto_user, cpf_user, obs_user, tipo_reg_user, reg_profissional_user, foto_usuario
        ) VALUES (
            :usuario_user, :login_user, :sexo_user, :idade_user, :email_user, :email02_user,
            :senha_user, :senha_default_user, :endereco_user, :numero_user, :cidade_user, :bairro_user,
            :estado_user, :telefone01_user, :telefone02_user, :data_create_user, :usuario_create_user,
            :fk_usuario_user, :ativo_user, :data_admissao_user, :vinculo_user, :nivel_user,
            :cargo_user, :depto_user, :cpf_user, :obs_user, :tipo_reg_user, :reg_profissional_user, :foto_usuario
        )";

        $insert = $this->conn->prepare($insertSql);
        $insert->bindValue(':usuario_user', 'Assistente Virtual');
        $insert->bindValue(':login_user', $login);
        $insert->bindValue(':sexo_user', 'n');
        $insert->bindValue(':idade_user', 0, PDO::PARAM_INT);
        $insert->bindValue(':email_user', 'assistente.virtual@fullcare.local');
        $insert->bindValue(':email02_user', null, PDO::PARAM_NULL);
        $insert->bindValue(':senha_user', $password);
        $insert->bindValue(':senha_default_user', 'assistente_virtual');
        $insert->bindValue(':endereco_user', 'Plataforma');
        $insert->bindValue(':numero_user', '0');
        $insert->bindValue(':cidade_user', 'Remoto');
        $insert->bindValue(':bairro_user', 'Centro');
        $insert->bindValue(':estado_user', 'SP');
        $insert->bindValue(':telefone01_user', null, PDO::PARAM_NULL);
        $insert->bindValue(':telefone02_user', null, PDO::PARAM_NULL);
        $insert->bindValue(':data_create_user', $now);
        $insert->bindValue(':usuario_create_user', 'system');
        $insert->bindValue(':fk_usuario_user', null, PDO::PARAM_NULL);
        $insert->bindValue(':ativo_user', 's');
        $insert->bindValue(':data_admissao_user', substr($now, 0, 10));
        $insert->bindValue(':vinculo_user', 'Assistente');
        $insert->bindValue(':nivel_user', 1, PDO::PARAM_INT);
        $insert->bindValue(':cargo_user', 'Assistente Virtual');
        $insert->bindValue(':depto_user', 'Suporte');
        $insert->bindValue(':cpf_user', '00000000000');
        $insert->bindValue(':obs_user', 'Usuario virtual para automacoes do chat interno.');
        $insert->bindValue(':tipo_reg_user', '');
        $insert->bindValue(':reg_profissional_user', '');
        $insert->bindValue(':foto_usuario', 'default-user.jpeg');

        $insert->execute();

        return (int) $this->conn->lastInsertId();
    }

    private function scoreEntry(string $question, array $entry): int
    {
        $score = 0;
        foreach ($entry['normalized_keywords'] ?? [] as $keyword) {
            if ($keyword && strpos($question, $keyword) !== false) {
                $score += strlen($keyword) >= 8 ? 3 : 2;
            }
        }

        return $score;
    }

    private function formatEntry(array $entry): string
    {
        $parts = [];
        $parts[] = '<strong>Assistente Virtual • ' . htmlspecialchars($entry['title']) . '</strong>';
        if (!empty($entry['summary'])) {
            $parts[] = htmlspecialchars($entry['summary']);
        }
        if (!empty($entry['steps']) && is_array($entry['steps'])) {
            $items = array_map(fn($step) => '<li>' . htmlspecialchars($step) . '</li>', $entry['steps']);
            $parts[] = '<ul>' . implode('', $items) . '</ul>';
        }

        if (!empty($entry['link'])) {
            $label = $entry['link_label'] ?? 'Ver referência';
            $url = $this->baseUrl . ltrim($entry['link'], '/');
            $parts[] = 'Referência: <a href="' . htmlspecialchars($url) . '" target="_blank" rel="noopener">' . htmlspecialchars($label) . '</a>';
        }

        $parts[] = '<em>Revise esta orientação antes de enviar ao cliente.</em>';

        return implode('<br>', $parts);
    }

    private function fallbackResponse(): string
    {
        $url = $this->baseUrl . 'manual.html';
        return '<strong>Assistente Virtual</strong><br>'
            . 'Ainda não tenho uma FAQ exata para este pedido. Informe hospital, paciente/conta e qual etapa está em dúvida '
            . 'para eu cruzar com materiais internos. Enquanto isso consulte o <a href="'
            . htmlspecialchars($url)
            . '" target="_blank" rel="noopener">Manual Geral</a> e valide com seu líder antes de responder ao cliente.';
    }

    private function normalizeText(string $text): string
    {
        $text = strtolower($text);
        $converted = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        if ($converted !== false) {
            $text = $converted;
        }
        $text = preg_replace('/[^a-z0-9\s]/', ' ', $text);
        return trim(preg_replace('/\s+/', ' ', $text));
    }
}
