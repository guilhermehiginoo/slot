import type { CourseClassGroup } from '@/types';

export function ClassGroupCard({
    classGroup,
}: {
    classGroup: CourseClassGroup;
}) {
    return (
        <article className="flex flex-col gap-3 rounded-xl border bg-card p-4">
            <header className="flex items-center justify-between gap-2">
                <h3 className="text-lg font-semibold">{classGroup.number}</h3>
                <span className="rounded-md bg-muted px-2 py-0.5 text-sm text-muted-foreground">
                    {classGroup.schedule}
                </span>
            </header>
            <dl className="grid gap-2 text-sm">
                <div>
                    <dt className="text-muted-foreground">Professores</dt>
                    <dd>{classGroup.professors.join(', ')}</dd>
                </div>
                <div>
                    <dt className="text-muted-foreground">Monitores</dt>
                    <dd>
                        {classGroup.monitors.length > 0
                            ? classGroup.monitors.join(', ')
                            : 'Sem monitores'}
                    </dd>
                </div>
            </dl>
        </article>
    );
}
