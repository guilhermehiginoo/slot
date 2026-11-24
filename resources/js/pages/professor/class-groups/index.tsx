import { Head } from '@inertiajs/react';
import ClassGroupController from '@/actions/App/Http/Controllers/Professor/ClassGroupController';
import AppLogoIcon from '@/components/app-logo-icon';
import { EmptyState } from '@/components/empty-state';
import { Pagination } from '@/components/pagination';
import { TaughtClassGroupList } from '@/components/professor/taught-class-group-list';
import type { Paginated, TaughtClassGroup } from '@/types';

type Props = {
    classGroups: Paginated<TaughtClassGroup>;
    currentSemester: string;
};

export default function ProfessorClassGroupsIndex({
    classGroups,
    currentSemester,
}: Props) {
    return (
        <>
            <Head title="Minhas turmas" />

            <div className="flex flex-1 flex-col gap-6 p-4 md:px-11">
                <header className="space-y-1">
                    <h1 className="text-3xl font-bold tracking-tight">
                        Minhas turmas
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        Semestre {currentSemester} · Apenas as turmas em que
                        você leciona.
                    </p>
                </header>

                {classGroups.total === 0 && (
                    <EmptyState
                        icon={<AppLogoIcon className="mb-2 size-10" />}
                        title="Nenhuma turma neste semestre"
                        description="As turmas em que você dá aula aparecem aqui quando o admin as cadastrar."
                    />
                )}

                {classGroups.total > 0 && (
                    <div className="flex flex-col gap-4 xl:gap-0 xl:overflow-hidden xl:rounded-xl xl:border xl:bg-card">
                        <TaughtClassGroupList classGroups={classGroups.data} />
                        <div className="xl:border-t xl:px-4 xl:py-3">
                            <Pagination page={classGroups} label="turmas" />
                        </div>
                    </div>
                )}
            </div>
        </>
    );
}

ProfessorClassGroupsIndex.layout = {
    breadcrumbs: [
        { title: 'Professor', href: ClassGroupController.index() },
        { title: 'Minhas turmas', href: ClassGroupController.index() },
    ],
};
