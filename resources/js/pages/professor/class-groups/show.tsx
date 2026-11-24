import { Head } from '@inertiajs/react';
import ClassGroupController from '@/actions/App/Http/Controllers/Professor/ClassGroupController';
import { InviteMonitorDialog } from '@/components/professor/invite-monitor-dialog';
import { PeopleSection } from '@/components/professor/people-section';
import { RemoveMonitorDialog } from '@/components/professor/remove-monitor-dialog';
import { RevokeInvitationButton } from '@/components/professor/revoke-invitation-button';
import { Badge } from '@/components/ui/badge';
import { formatDate } from '@/lib/dates';
import { invitationStatusLabels } from '@/lib/invitation-status';
import type {
    ClassGroupDetails,
    ClassGroupInvitation,
    ClassMonitor,
    MonitorCandidate,
    PendingApplication,
} from '@/types';

type Props = {
    classGroup: ClassGroupDetails;
    monitors: ClassMonitor[];
    invitations: ClassGroupInvitation[];
    applications: PendingApplication[];
    search: string;
    candidates: MonitorCandidate[];
};

export default function ProfessorClassGroupShow({
    classGroup,
    monitors,
    invitations,
    applications,
    search,
    candidates,
}: Props) {
    const title = `${classGroup.course.code} · ${classGroup.number}`;

    return (
        <>
            <Head title={title} />

            <div className="flex flex-1 flex-col gap-8 p-4 md:px-11">
                <header className="flex flex-col gap-3">
                    <div className="space-y-1">
                        <h1 className="text-3xl font-bold tracking-tight">
                            {title}
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            {classGroup.course.name} · {classGroup.semester} ·{' '}
                            {classGroup.schedule}
                        </p>
                    </div>
                    <ul className="flex flex-wrap gap-2">
                        {classGroup.professors.map((professor) => (
                            <li key={professor.id}>
                                <Badge variant="secondary">
                                    {professor.name}
                                </Badge>
                            </li>
                        ))}
                        <li>
                            <Badge variant="secondary">
                                {classGroup.semester}
                            </Badge>
                        </li>
                    </ul>
                    <InviteMonitorDialog
                        classGroupId={classGroup.id}
                        search={search}
                        candidates={candidates}
                    />
                </header>

                <PeopleSection
                    title="Monitores atuais"
                    columns={['Monitor desde']}
                    emptyMessage="Nenhum monitor nesta turma."
                    rows={monitors.map((monitor) => ({
                        id: monitor.id,
                        name: monitor.name,
                        details: [formatDate(monitor.appointed_at)],
                        action: (
                            <RemoveMonitorDialog
                                classGroupId={classGroup.id}
                                monitor={monitor}
                            />
                        ),
                    }))}
                />

                <PeopleSection
                    title="Convites"
                    columns={['Enviado em', 'Status']}
                    emptyMessage="Nenhum convite enviado."
                    rows={invitations.map((invitation) => ({
                        id: invitation.id,
                        name: invitation.student.name,
                        details: [
                            formatDate(invitation.created_at),
                            invitationStatusLabels[invitation.status],
                        ],
                        action: invitation.status === 'pending' && (
                            <RevokeInvitationButton
                                invitationId={invitation.id}
                            />
                        ),
                    }))}
                />

                <PeopleSection
                    title="Solicitações pendentes"
                    columns={['Mensagem', 'Enviada em']}
                    emptyMessage="Nenhuma solicitação pendente."
                    rows={applications.map((application) => ({
                        id: application.id,
                        name: application.student.name,
                        details: [
                            application.message ?? 'Sem mensagem.',
                            formatDate(application.created_at),
                        ],
                    }))}
                />
            </div>
        </>
    );
}

ProfessorClassGroupShow.layout = {
    breadcrumbs: [
        { title: 'Professor', href: ClassGroupController.index() },
        { title: 'Minhas turmas', href: ClassGroupController.index() },
    ],
};
