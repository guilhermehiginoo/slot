import { Form, Head } from '@inertiajs/react';
import { Plus, Search } from 'lucide-react';
import { useState } from 'react';
import CourseController from '@/actions/App/Http/Controllers/Admin/CourseController';
import { ArchiveCourseDialog } from '@/components/admin/archive-course-dialog';
import { CourseFormDialog } from '@/components/admin/course-form-dialog';
import { CourseList } from '@/components/admin/course-list';
import { EmptyState } from '@/components/empty-state';
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
    const [formOpen, setFormOpen] = useState(false);
    const [editing, setEditing] = useState<AdminCourse | null>(null);

    const openForm = (course: AdminCourse | null) => {
        setEditing(course);
        setFormOpen(true);
    };

    const newCourseButton = (
        <Button
            className="h-[46px] min-h-11 gap-2 rounded-xl px-3.5 has-[>svg]:px-3.5 md:w-[213px]"
            onClick={() => openForm(null)}
        >
            <Plus />
            Nova disciplina
        </Button>
    );

    return (
        <>
            <Head title="Disciplinas" />

            <div className="flex flex-1 flex-col gap-6 p-4">
                <div className="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <header className="space-y-1">
                        <h1 className="text-3xl font-bold tracking-tight">
                            Disciplinas
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Gerencie as disciplinas disponíveis para monitoria.
                        </p>
                    </header>
                    {newCourseButton}
                </div>

                <Form
                    {...CourseController.index.form()}
                    options={{ preserveState: true, replace: true }}
                    className="relative md:max-w-xl"
                >
                    <Search className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-primary" />
                    <Label htmlFor="search" className="sr-only">
                        Buscar por código ou nome
                    </Label>
                    <Input
                        id="search"
                        name="search"
                        type="search"
                        defaultValue={filters.search ?? ''}
                        placeholder="Buscar por código ou nome"
                        className="h-11 bg-card pl-9"
                    />
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
                    <div className="flex flex-col gap-4 md:gap-0 md:overflow-hidden md:rounded-xl md:border md:bg-card">
                        <CourseList
                            courses={courses.data}
                            onEdit={openForm}
                            onArchive={setArchiving}
                        />
                        <div className="md:border-t md:px-4 md:py-3">
                            <Pagination page={courses} label="disciplinas" />
                        </div>
                    </div>
                )}
            </div>

            <CourseFormDialog
                open={formOpen}
                course={editing}
                onClose={() => setFormOpen(false)}
            />

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
