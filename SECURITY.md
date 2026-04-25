# Segurança

## Reportar uma vulnerabilidade

Encontrou algum problema de segurança? Abre uma issue privada no GitHub ou manda por email pra cdajuniorf@gmail.com com:

- descrição do problema
- passos para reproduzir
- versão/commit em que aconteceu
- impacto esperado

Vou tentar responder em até 7 dias. Por favor não abre PR público com a correção antes da gente conversar.

## O que o projeto faz pra reduzir risco

Não é um sistema crítico, mas algumas decisões valem comentar:

- Autenticação via Laravel Sanctum (Bearer token). Token novo a cada login, tokens antigos do mesmo usuário são revogados.
- Autorização por papel (admin / operador) feita em Policies. Admin tem ability global via `Gate::before`.
- Login e registro com rate limit de 5/min por email+IP. API autenticada com 60/min por usuário.
- Mensagem uniforme em falha de login (mesma resposta para email inexistente vs senha errada) e Timebox de 500ms para evitar timing oracle.
- Senhas hash via bcrypt com 12 rounds em produção (4 em testes para velocidade).
- Headers de segurança em todas as respostas: X-Content-Type-Options, X-Frame-Options DENY, Referrer-Policy, Permissions-Policy, Content-Security-Policy `default-src 'none'; frame-ancestors 'none'`. HSTS condicional a produção sobre HTTPS.
- CORS sem wildcard. Origens permitidas via env `CORS_ALLOWED_ORIGINS`.
- Validação centralizada em Form Requests com `authorize()` real chamando Policy.
- `$fillable` explícito em todo modelo. Sem `$guarded = []`.
- `Model::preventLazyLoading` em ambiente não-produção pra flagrar N+1 cedo.
- Soft delete em todas as entidades — exclusão preserva histórico.

## O que ainda não está coberto

- Senha sem requisitos de complexidade ou checagem contra senhas vazadas.
- CPF em texto plano no banco (decisão consciente pra esse exercício, mas em produção real seria criptografado).
- Sem 2FA.
- Sem expiração de token Sanctum (Sanctum::expiration null).
- Logs sensíveis: o handler global do Laravel pode acabar logando payload em caso de exception.

Se for usar isso em produção de verdade, abre uma issue antes — várias dessas decisões mudariam.
