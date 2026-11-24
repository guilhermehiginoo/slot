import { Head } from '@inertiajs/react';
import CourseController from '@/actions/App/Http/Controllers/Admin/CourseController';
import { CourseForm } from '@/components/admin/course-form';
import Heading from '@/components/heading';

export default function CoursesCreate() {
    return (
        <>
            <Head title="Nova disciplina" />

            <div className="flex flex-1 flex-col p-4">
                <Heading
                    title="Nova disciplina"
                    description="Cadastre uma disciplina no catálogo acadêmico."
                />
                <CourseForm form={CourseController.store.form()} />
            </div>
        </>
    );
}

CoursesCreate.layout = {
    breadcrumbs: [
        { title: 'Disciplinas', href: CourseController.index() },
        { title: 'Nova disciplina', href: CourseController.create() },
    ],
};
