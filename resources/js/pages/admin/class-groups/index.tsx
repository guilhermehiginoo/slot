import { Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { useState } from 'react';
import ClassGroupController from '@/actions/App/Http/Controllers/Admin/ClassGroupController';
import { ClassGroupFilters } from '@/components/admin/class-group-filters';
import { ClassGroupList } from '@/components/admin/class-group-list';
import { EmptyState } from '@/components/empty-state';
import { Pagination } from '@/components/pagination';
import { Button } from '@/components/ui/button';
import type {
    AdminClassGroup,
    CourseOption,
    Paginated,
    ProfessorOption,
} from '@/types';

type Props = {
    classGroups: Paginated<AdminClassGroup>;
    filters: {
        course: number | null;
        semester: string;
    };
    currentSemester: string;
    hasClassGroups: boolean;
    courses: CourseOption[];
    professors: ProfessorOption[];
    semesters: string[];
};

export default function ClassGroupsIndex({
    classGroups,
    filters,
    currentSemester,
    hasClassGroups,
    courses,
    semesters,
}: Props) {
    const [, setEditing] = useState<AdminClassGroup | null>(null);

    const newClassGroupButton = (
        <Button
            className="h-[46px] min-h-11 gap-2 rounded-xl px-3.5 has-[>svg]:px-3.5 md:w-[213px]"
            onClick={() => setEditing(null)}
        >
            <Plus />
            Nova turma
        </Button>
    );

    const filtered =
        filters.course !== null || filters.semester !== currentSemester;

    const clearFiltersButton = (
        <Button variant="outline" className="h-11" asChild>
            <Link href={ClassGroupController.index()}>Limpar filtros</Link>
        </Button>
    );

    return (
        <>
            <Head title="Turmas" />

            <div className="flex flex-1 flex-col gap-6 p-4 md:px-11">
                <div className="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <header className="space-y-1">
                        <h1 className="text-3xl font-bold tracking-tight">
                            Turmas
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Gerencie as turmas e seus professores no semestre.
                        </p>
                    </header>
                    {newClassGroupButton}
                </div>

                <ClassGroupFilters
                    filters={filters}
                    courses={courses}
                    semesters={semesters}
                />

                {filtered && hasClassGroups && classGroups.total > 0 && (
                    <div>{clearFiltersButton}</div>
                )}

                {!hasClassGroups && (
                    <EmptyState
                        title="Nenhuma turma cadastrada"
                        description="Cadastre a primeira turma para vincular professores e monitores."
                        action={newClassGroupButton}
                    />
                )}

                {hasClassGroups && classGroups.total === 0 && (
                    <EmptyState
                        title="Nenhuma turma encontrada"
                        description="Altere a disciplina ou o semestre para tentar novamente."
                        action={clearFiltersButton}
                    />
                )}

                {classGroups.total > 0 && (
                    <div className="flex flex-col gap-4 md:gap-0 md:overflow-hidden md:rounded-xl md:border md:bg-card">
                        <ClassGroupList
                            classGroups={classGroups.data}
                            onEdit={setEditing}
                        />
                        <div className="md:border-t md:px-4 md:py-3">
                            <Pagination page={classGroups} label="turmas" />
                        </div>
                    </div>
                )}
            </div>
        </>
    );
}

ClassGroupsIndex.layout = {
    breadcrumbs: [
        { title: 'Administração', href: ClassGroupController.index() },
        { title: 'Turmas', href: ClassGroupController.index() },
    ],
};
