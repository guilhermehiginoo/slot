import { Form, Head } from '@inertiajs/react';
import CourseController from '@/actions/App/Http/Controllers/CourseController';
import { CourseResultList } from '@/components/courses/course-result-list';
import { EmptyState } from '@/components/empty-state';
import { Pagination } from '@/components/pagination';
import { SearchIcon } from '@/components/search-icon';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { CourseSearchResult, Paginated } from '@/types';

type Props = {
    courses: Paginated<CourseSearchResult>;
    filters: {
        search: string | null;
    };
};

export default function CoursesIndex({ courses, filters }: Props) {
    return (
        <>
            <Head title="Buscar disciplinas" />

            <div className="flex flex-1 flex-col gap-6 p-4 md:px-11">
                <header className="space-y-1">
                    <h1 className="text-3xl font-bold tracking-tight">
                        Buscar disciplinas
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        Encontre as turmas, os professores e os monitores de uma
                        disciplina.
                    </p>
                </header>

                <Form
                    {...CourseController.index.form()}
                    options={{ preserveState: true, replace: true }}
                    className="relative md:max-w-xl"
                >
                    <SearchIcon className="pointer-events-none absolute top-1/2 left-3.5 h-[15px] w-3.5 -translate-y-1/2 text-primary" />
                    <Label htmlFor="search" className="sr-only">
                        Buscar por código ou nome
                    </Label>
                    <Input
                        id="search"
                        name="search"
                        type="search"
                        defaultValue={filters.search ?? ''}
                        placeholder="Buscar por código ou nome"
                        className="h-11 bg-card pl-10"
                    />
                </Form>

                {courses.total === 0 && filters.search !== null && (
                    <EmptyState
                        title="Nenhuma disciplina encontrada"
                        description="Busque pelo código, como IMD0030, ou por parte do nome."
                    />
                )}

                {courses.total === 0 && filters.search === null && (
                    <EmptyState
                        title="Nenhuma disciplina disponível"
                        description="As disciplinas aparecem aqui quando o admin as cadastrar."
                    />
                )}

                {courses.total > 0 && (
                    <div className="flex flex-col gap-4">
                        <CourseResultList courses={courses.data} />
                        <Pagination page={courses} label="disciplinas" />
                    </div>
                )}
            </div>
        </>
    );
}

CoursesIndex.layout = {
    breadcrumbs: [
        { title: 'Buscar disciplinas', href: CourseController.index() },
    ],
};
