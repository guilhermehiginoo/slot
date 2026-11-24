import { Head } from '@inertiajs/react';
import ClassGroupController from '@/actions/App/Http/Controllers/Professor/ClassGroupController';
import { PeopleSection } from '@/components/professor/people-section';
import { Badge } from '@/components/ui/badge';
import { formatDate } from '@/lib/dates';
import type {
    ClassGroupDetails,
    ClassMonitor,
    PendingApplication,
    PendingInvitation,
} from '@/types';

type Props = {
    classGroup: ClassGroupDetails;
    monitors: ClassMonitor[];
    invitations: PendingInvitation[];
    applications: PendingApplication[];
};

export default function ProfessorClassGroupShow({
    classGroup,
    monitors,
    invitations,
    applications,
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
                </header>

                <PeopleSection
                    title="Monitores atuais"
                    columns={['Monitor desde']}
                    emptyMessage="Nenhum monitor nesta turma."
                    rows={monitors.map((monitor) => ({
                        id: monitor.id,
                        name: monitor.name,
                        details: [formatDate(monitor.appointed_at)],
                    }))}
                />

                <PeopleSection
                    title="Convites pendentes"
                    columns={['Enviado em', 'Status']}
                    emptyMessage="Nenhum convite pendente."
                    rows={invitations.map((invitation) => ({
                        id: invitation.id,
                        name: invitation.student.name,
                        details: [
                            formatDate(invitation.created_at),
                            'Pendente',
                        ],
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
