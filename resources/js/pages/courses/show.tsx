import { Head, usePage } from '@inertiajs/react';
import CourseController from '@/actions/App/Http/Controllers/CourseController';
import { ClassGroupCard } from '@/components/courses/class-group-card';
import { EmptyState } from '@/components/empty-state';
import type { CourseClassGroup, CourseDetails } from '@/types';

type Props = {
    course: CourseDetails;
    classGroups: CourseClassGroup[];
    pastProfessors: string[];
};

export default function CourseShow({
    course,
    classGroups,
    pastProfessors,
}: Props) {
    const { currentSemester } = usePage().props;

    return (
        <>
            <Head title={course.code} />

            <div className="flex flex-1 flex-col gap-8 p-4 md:px-11">
                <header className="space-y-1">
                    <h1 className="text-3xl font-bold tracking-tight">
                        {course.code}
                    </h1>
                    <p className="text-muted-foreground">{course.name}</p>
                </header>

                {course.description && (
                    <p className="max-w-3xl whitespace-pre-line">
                        {course.description}
                    </p>
                )}

                <section className="flex flex-col gap-4">
                    <h2 className="text-xl font-semibold">
                        Turmas em {currentSemester}
                    </h2>
                    {classGroups.length > 0 ? (
                        <div className="grid gap-4 md:grid-cols-2">
                            {classGroups.map((classGroup) => (
                                <ClassGroupCard
                                    key={classGroup.id}
                                    classGroup={classGroup}
                                />
                            ))}
                        </div>
                    ) : (
                        <EmptyState
                            title="Nenhuma turma neste semestre"
                            description="As turmas aparecem aqui quando o admin as cadastrar."
                        />
                    )}
                </section>

                {pastProfessors.length > 0 && (
                    <section className="flex flex-col gap-4">
                        <h2 className="text-xl font-semibold">
                            Professores anteriores
                        </h2>
                        <ul className="flex flex-wrap gap-2">
                            {pastProfessors.map((name) => (
                                <li
                                    key={name}
                                    className="rounded-full border bg-card px-3 py-1 text-sm"
                                >
                                    {name}
                                </li>
                            ))}
                        </ul>
                    </section>
                )}
            </div>
        </>
    );
}

CourseShow.layout = {
    breadcrumbs: [
        { title: 'Buscar disciplinas', href: CourseController.index() },
    ],
};
