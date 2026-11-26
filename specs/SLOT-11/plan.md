# Plano · SLOT-11

- Persistir horários em `slots` com turma, monitor, datas UTC, local, vagas e data de cancelamento. Indexar por monitor e início e por turma e início.
- Usar uma policy de horário para validar o vínculo ativo e a propriedade. Proteger as escritas concorrentes no mesmo monitor com transação e trava na linha do usuário.
- Usar um FormRequest para validar datas, local e vagas. Converter datas locais de Natal para UTC e validar conflitos de todas as ocorrências antes de gravar uma série.
- Expor a gestão em Minhas monitorias, com lista, formulário e confirmação de cancelamento. A remoção do vínculo cancela os horários futuros na mesma transação.
- Cobrir os quatro cenários do Jira em testes de feature, além de recorrência, edição, cancelamento e remoção. A gestão de reservas e os e-mails seguem as dependências da spec.
