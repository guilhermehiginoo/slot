# Plano · SLOT-10

## Banco

- Sem migration nova: `class_monitor` e `monitor_invitations` vêm da SLOT-9. A unicidade do convite pendente (R3) é checada no FormRequest; a pivô já tem `primary(['class_group_id', 'user_id'])`.
- `User`: `monitorInvitations(): HasMany` (por `user_id`), para o dashboard.

## Backend

| Rota                                                        | Controller@método                                      | Autorização                         |
| ----------------------------------------------------------- | ------------------------------------------------------ | ----------------------------------- |
| `GET /professor/turmas/{classGroup}?busca=`                 | `Professor\ClassGroupController@show` (+ `candidates`) | `view` na turma                     |
| `POST /professor/turmas/{classGroup}/convites`              | `Professor\MonitorInvitationController@store`          | `manageMonitors` na turma (R1)      |
| `DELETE /professor/convites/{monitorInvitation}`            | `Professor\MonitorInvitationController@destroy`        | `revoke` no convite (R1, R5)        |
| `DELETE /professor/turmas/{classGroup}/monitores/{monitor}` | `Professor\ClassMonitorController@destroy`             | `manageMonitors` na turma (R1, R10) |
| `GET /visao-geral`                                          | `DashboardController@index`                            | `auth`                              |
| `PATCH /convites/{monitorInvitation}`                       | `MonitorInvitationController@update` (`status`)        | `respond` no convite (R6)           |

- `ClassGroupPolicy::manageMonitors` = `view`. `MonitorInvitationPolicy`: `revoke` = professor da turma; `respond` = o aluno convidado.
- FormRequests: `Professor\InviteMonitorRequest` (`user_id` existe, é aluno, não é monitor da turma, sem convite pendente — R2, R3) e `RespondMonitorInvitationRequest` (`status` em `accepted|declined`).
- Actions em `app/Actions/Monitoring/`: `InviteMonitor` (cria e notifica), `RevokeMonitorInvitation`, `RespondToMonitorInvitation` (transação, `lockForUpdate`, confere pendente, cria a pivô no aceite — R4, R7) e `RemoveClassMonitor`.
- Notificação `MonitorInvitationReceived` (`ShouldQueue`, canal `mail`) — R8.

## Queries

- `show`: convites passam a listar todos os status, menos `accepted` (o aceito vira monitor); `candidates` só com `busca` de 2+ caracteres: alunos por nome ou e-mail, sem monitores e sem convite pendente da turma, `limit(10)`.
- Dashboard: convites pendentes do aluno com `classGroup.course` (`select` só do que a tela usa).

## Frontend

- `show.tsx`: botão "Convidar aluno" abre `components/professor/invite-monitor-dialog.tsx` (busca com reload parcial de `candidates`); seções ganham ações "Remover" (com confirmação) e "Revogar".
- `PeopleSection` ganha `actions` opcional por linha.
- `dashboard.tsx`: bloco "Convites de monitoria" (`components/monitoring/invitation-list.tsx`) com Aceitar e Recusar.
- Status: `lib/invitation-status.ts` com rótulos em português.

## Testes

`tests/Feature/Monitoring/MonitorInvitationTest` e `RemoveMonitorTest`, um teste por cenário da spec, mais autorização, e-mail em fila (`Notification::fake`), convite não pendente, monitor de várias turmas. Atualizar o teste da SLOT-9 que esconde convites não pendentes.
