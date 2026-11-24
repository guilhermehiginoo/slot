import { Form, Head, Link } from '@inertiajs/react';
import { Plus, Search } from 'lucide-react';
import { useState } from 'react';
import CourseController from '@/actions/App/Http/Controllers/Admin/CourseController';
import { ArchiveCourseDialog } from '@/components/admin/archive-course-dialog';
import { CourseList } from '@/components/admin/course-list';
import { EmptyState } from '@/components/empty-state';
import Heading from '@/components/heading';
import { Pagination } from '@/components/pagination';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { AdminCourse, Paginated } from '@/types';

type Props = {
    courses: Paginated<AdminCourse>;
    filters: {
        search: string | null;
    };
};

export default function CoursesIndex({ courses, filters }: Props) {
    const [archiving, setArchiving] = useState<AdminCourse | null>(null);

    const newCourseButton = (
        <Button asChild>
            <Link href={CourseController.create()}>
                <Plus />
                Nova disciplina
            </Link>
        </Button>
    );

    return (
        <>
            <Head title="Disciplinas" />

            <div className="flex flex-1 flex-col gap-6 p-4">
                <div className="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <Heading
                        title="Disciplinas"
                        description="Gerencie as disciplinas disponíveis para monitoria."
                    />
                    {newCourseButton}
                </div>

                <Form
                    {...CourseController.index.form()}
                    options={{ preserveState: true, replace: true }}
                    className="flex gap-2 md:max-w-xl"
                >
                    <Label htmlFor="search" className="sr-only">
                        Buscar por código ou nome
                    </Label>
                    <Input
                        id="search"
                        name="search"
                        type="search"
                        defaultValue={filters.search ?? ''}
                        placeholder="Buscar por código ou nome"
                    />
                    <Button type="submit" variant="secondary">
                        <Search />
                        <span className="sr-only md:not-sr-only">Buscar</span>
                    </Button>
                </Form>

                {courses.total === 0 && filters.search === null && (
                    <EmptyState
                        title="Nenhuma disciplina cadastrada"
                        description="Comece cadastrando a primeira disciplina do catálogo."
                        action={newCourseButton}
                    />
                )}

                {courses.total === 0 && filters.search !== null && (
                    <EmptyState
                        title="Nenhuma disciplina encontrada"
                        description="Tente buscar por outro código ou nome."
                    />
                )}

                {courses.total > 0 && (
                    <CourseList
                        courses={courses.data}
                        onArchive={setArchiving}
                    />
                )}

                <Pagination page={courses} label="disciplinas" />
            </div>

            <ArchiveCourseDialog
                course={archiving}
                onClose={() => setArchiving(null)}
            />
        </>
    );
}

CoursesIndex.layout = {
    breadcrumbs: [
        { title: 'Administração', href: CourseController.index() },
        { title: 'Disciplinas', href: CourseController.index() },
    ],
};
