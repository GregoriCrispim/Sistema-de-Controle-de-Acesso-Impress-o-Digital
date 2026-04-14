# Guia de Testes — AlunoBem

Documento com instruções para rodar os testes automatizados e para testar manualmente as funcionalidades do sistema.

---

## Testes Automatizados

### Rodar todos os testes

```bash
docker exec php_alunobem php artisan test
```

### Rodar suites específicas

```bash
# Comparação biométrica (parsing ISO 19794-2, matching, performance)
docker exec php_alunobem php artisan test --filter=FingerprintMatcherTest

# Liberação por biometria (fluxo completo HTTP)
docker exec php_alunobem php artisan test --filter=BiometricReleaseTest

# Liberação manual
docker exec php_alunobem php artisan test --filter=ManualReleaseTest

# Autenticação e login
docker exec php_alunobem php artisan test --filter=AuthenticationTest

# Controle de acesso por perfil
docker exec php_alunobem php artisan test --filter=RoleAccessTest

# Relatórios e exports CSV
docker exec php_alunobem php artisan test --filter=ReportTest

# Configurações do sistema
docker exec php_alunobem php artisan test --filter=SettingsTest

# Gestão de alunos (CRUD, LGPD)
docker exec php_alunobem php artisan test --filter=StudentManagementTest

# Validação fiscal
docker exec php_alunobem php artisan test --filter=FiscalValidationTest

# Ocorrências
docker exec php_alunobem php artisan test --filter=OccurrenceTest

# Regras de voucher de refeição
docker exec php_alunobem php artisan test --filter=VoucherRulesTest
```

### O que cada suite verifica

| Suite | Testes | Descrição |
|---|---|---|
| **FingerprintMatcherTest** | 18 | Parsing ISO 19794-2, self-match, variantes (re-captura), rejeição de digitais diferentes, busca em lista, níveis de segurança 1-7, performance com 1000 registros |
| **BiometricReleaseTest** | 8 | Liberação por biometria real, digital desconhecida, hex inválido, aluno inativo, duplicata no dia, audit log, match correto entre múltiplos alunos |
| **ManualReleaseTest** | 6 | Liberação manual com motivo, validações obrigatórias, limite percentual de manuais |
| **AuthenticationTest** | 8 | Login/logout, redirecionamento por perfil, usuário inativo, login Google do fiscal |
| **RoleAccessTest** | 8 | Isolamento de acesso (operador, empresa, fiscal, gestão, admin) |
| **ReportTest** | 12 | Relatórios diário, mensal, por aluno, por operador, exceções, pagamento + CSV |
| **StudentManagementTest** | 6 | CRUD de alunos, matrícula única, limite de 3 digitais, anonimização LGPD |
| **FiscalValidationTest** | 5 | Validação de período, protocolo, cálculo de valor, bloqueio de duplicata |
| **OccurrenceTest** | 4 | Registro de ocorrências, tipos válidos, vínculo aluno/operador |
| **SettingsTest** | 6 | CRUD de configurações, audit log de alterações, dias letivos |
| **VoucherRulesTest** | 6 | Uma refeição por dia, aluno inativo bloqueado, escopos de período |

**Total: 89 testes, 497 assertions.**

---

## Credenciais de Login

| Perfil | E-mail | Senha |
|---|---|---|
| Admin | `admin@alunobem.com` | `admin123` |
| Operador | `operador@alunobem.com` | `operador123` |
| Empresa | `empresa@alunobem.com` | `empresa123` |
| Fiscal | `gregoridesbravador@gmail.com` | `fiscal123` |
| Gestão | `gestao@alunobem.com` | `gestao123` |

---

## Teste Manual: Liberação de Almoço por Biometria

### Funcionamento

1. O script Python roda localmente na máquina Windows com o leitor Suprema BioMini
2. O aluno coloca o dedo → o script captura a digital e cola o código HEX no campo focado do navegador
3. O Laravel recebe o HEX e compara contra todas as digitais cadastradas (ISO 19794-2 minutiae matching em PHP)
4. Se corresponder, a refeição é liberada

### Simulando sem o leitor

É possível testar colando diretamente um template HEX no campo do terminal.

**Passo a passo:**

1. Acesse `http://localhost:8080` e faça login como **Operador** (`operador@alunobem.com` / `operador123`)
2. No **Terminal do Operador**, clique no campo "Aguardando digital..."
3. Cole um dos templates abaixo e aguarde ~1 segundo

### Templates de Teste

**João Pedro Silva (matrícula 2026001) — deve liberar:**

```
464D520020323000000000D400000100016800C500C501000000501E0095009D5B36801D0105B330801100B1133480D9004DBB338081004D732900B900ED8B3300F101050B4B009D00F9534980D501519B2A00810159C35100B90121E34140A9006D7B4240A5002DA337402900718B5B404500294B4080F100E94334001500CDDB4C40ED0139334280D1000D532A00C500C93B32408500E9F357802D0081EB2A80210035EB5F00C90125D36380A100991B3F00C1009D6341401D0099035500E10085FB3E406500D9433680B1002DAB2E00000000
```

> Resultado: tela verde **LIBERADO** — "João Pedro Silva", matrícula 2026001, turma 1º A.

**Maria Eduarda Santos (matrícula 2026002) — deve liberar:**

```
464D520020323000000000D400000100016800C500C501000000501E00F20070404480340076AE52806E00C4FC3840BC004E162940B200948861806C01321E2E807600548C4B80C0009A264B80E60128D05F0048003A0E2800BA00DC9C5F807C0026465080F20150E82F8014009E7E57402A00B80C58009000BE364340420130E028805000924E59003200B03C5D40C000BA965D80DE00A4E82B801000725E5B40A60054AC5940A400EEC63040E60084104C802800D2AE58401A01385C530010009EE64B00E60034C831402C01463E2A00000000
```

> Resultado: tela verde **LIBERADO** — "Maria Eduarda Santos".

**Digital não cadastrada — deve bloquear:**

```
464D520020323000000000D400000100016800C500C501000000501E80C70053353900A30053CD62807300DFB94B002F00B749624047009FE54740C3013BAD390093006F715540F3007F214B004B00A325318037005B8D2F40BB00633956004B0023892E007F00C3753440DB01032D6240CF012FE143007B0083514C00BB00CB353C405B01372D2D008700A3B95240D70123A92A805B0093853A00A700B74D61403B009F7134804F0107813100430117C5490087001F2D5C405B00E33933402B0033293380F3008F752880E7014B8D4200000000
```

> Resultado: tela vermelha **BLOQUEADO** — "Digital não cadastrada".

**Duplicata no mesmo dia:**

Cole novamente o template do João Pedro Silva → resultado: **BLOQUEADO** — "Já almoçou hoje".

---

## Teste Manual: Liberação Manual

1. No terminal, clique em **"Buscar Aluno (Manual)"**
2. Digite parte do nome ou matrícula (ex: `João` ou `2026001`)
3. Clique no aluno encontrado
4. Preencha o motivo e confirme a liberação

> O sistema bloqueia liberações manuais acima de 30% do total do dia.

---

## Teste Manual: Cadastro de Digitais

1. Faça login como **Admin** (`admin@alunobem.com` / `admin123`)
2. Vá em **Admin → Alunos → Editar** qualquer aluno
3. Na seção "Impressões Digitais", cole um template HEX no campo
4. Selecione o dedo e clique em **Salvar Digital**
5. Cada aluno aceita no máximo **3 digitais**

---

## Alunos de Teste (Seeder)

O seeder cria 20 alunos com digitais ISO 19794-2 válidas, histórico de 30 dias de refeições e ocorrências.

| Matrícula | Nome | Turma | Curso | Digitais |
|---|---|---|---|---|
| 2026001 | João Pedro Silva | 1º A | Ensino Médio | 2 (dedo 1, 2) |
| 2026002 | Maria Eduarda Santos | 2º A | Ensino Médio | 2 (dedo 1, 2) |
| 2026003 | Lucas Gabriel Oliveira | 1º B | Ensino Médio | 2 (dedo 1, 2) |
| 2026004 | Ana Beatriz Ferreira | 2º B | Ensino Médio | 2 (dedo 1, 2) |
| 2026005 | Pedro Henrique Costa | 1º A | Ensino Médio | 2 (dedo 1, 2) |
| 2026006 | Isabela Cristina Lima | 1º P | PROEJA | 2 (dedo 1, 2) |
| 2026007 | Gabriel Souza Martins | 3º A | Ensino Médio | 2 (dedo 1, 2) |
| 2026008 | Larissa Mendes Rocha | 3º A | Ensino Médio | 2 (dedo 1, 2) |
| 2026009 | Matheus Almeida Ribeiro | 2º P | PROEJA | 2 (dedo 1, 2) |
| 2026010 | Camila Rodrigues Araújo | 1º B | Ensino Médio | 2 (dedo 1, 2) |
| 2026011 | Rafael Torres Barbosa | 2º A | Ensino Médio | 1 (dedo 1) |
| 2026012 | Juliana Pereira Nunes | 1º P | PROEJA | 1 (dedo 1) |
| 2026013 | Felipe Cardoso Dias | 1º A | Ensino Médio | 1 (dedo 1) |
| 2026014 | Beatriz Monteiro Gomes | 3º B | Ensino Médio | 1 (dedo 1) |
| 2026015 | Thiago Nascimento Pinto | 2º P | PROEJA | 1 (dedo 1) |
| 2026016 | Valentina Castro Moreira | 1º B | Ensino Médio | 1 (dedo 1) |
| 2026017 | Enzo Miguel Correia | 2º B | Ensino Médio | 1 (dedo 1) |
| 2026018 | Sofia Helena Teixeira | 1º A | Ensino Médio | 1 (dedo 1) |
| 2026019 | Arthur Vieira Campos | 1º P | PROEJA | 1 (dedo 1) |
| 2026020 | Manuela Freitas Azevedo | 3º A | Ensino Médio | 1 (dedo 1) |

---

## Configurações do Sistema

Acessíveis em **Admin → Configurações**:

| Configuração | Padrão | Descrição |
|---|---|---|
| Horário de início | 10:00 | Início do período de almoço |
| Horário de fim | 15:00 | Fim do período de almoço |
| Valor da refeição | R$ 15,00 | Valor unitário para relatórios de pagamento |
| Limite manual | 30% | Máximo de liberações manuais sobre o total do dia |

> Para testar fora do horário padrão, altere o horário nas configurações antes de usar o terminal.
