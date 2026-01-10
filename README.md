# FullConex

## Controle de versões do banco

O projeto agora garante automaticamente a presença da tabela `schema_version` em cada solicitação (veja `app/schemaEnsurer.php`) para registrar quais migrações já foram aplicadas. A tabela tem esta estrutura:

| Coluna | Descrição |
| --- | --- |
| `id` | Chave primária incremental. |
| `version` | Identificador único da migração (`20231201_add_column_x`, `v1.2.0` etc.). |
| `description` | Texto explicando o objetivo da migração. |
| `applied_at` | Timestamp automática do momento da aplicação. |
| `applied_by` | Nome ou e-mail de quem executou a migração. |
| `file_name` | Nome do script de migração (caso haja). |
| `checksum` | Hash opcional do script executado para detectar alterações. |

### Como usar

1. Crie um script numerado (ex.: `scripts/migrations/20240101_create_schema_version.php`) ou SQL correspondente com as alterações do banco.
2. Antes de rodar em produção, registre manualmente a migração (ou o script pode inseri-la ao final):

```sql
INSERT INTO schema_version (version, description, applied_by, file_name, checksum)
VALUES ('20240101_create_schema_version', 'Cria a tabela schema_version', 'seu_nome', '20240101_create_schema_version.php', 'md5-do-script');
```

3. Sempre que precisar validar, consulte `schema_version` para confirmar o histórico. Em deployments automatizados, as novas migrações devem inserir a linha correspondente após o `ALTER`/`CREATE`.

### Integrações futuras

- Poderia-se adicionar um runner simples em PHP que percorre `scripts/migrations/` e executa apenas as entradas ausentes na tabela.
- Tags de release no Git podem ser espelhadas em `schema_version.version` para manter controle da aplicação + banco.

### Versão na interface

- O rodapé da tela de login já exibe a versão atual (`Versão v1.3.0`). Esse número vem de `app/version.php`, que consulta `schema_version` e usa `APP_VERSION_DEFAULT` como fallback.
- Para subir a versão visível altere `APP_VERSION_DEFAULT` nesse arquivo e registre a migração com o mesmo identificador semântico (por exemplo `v1.3.1`). A próxima vez que a tabela for lida, o novo valor aparece automaticamente. 
