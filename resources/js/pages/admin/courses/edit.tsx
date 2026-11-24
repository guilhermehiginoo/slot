import { Head } from '@inertiajs/react';
import CourseController from '@/actions/App/Http/Controllers/Admin/CourseController';
import { CourseForm } from '@/components/admin/course-form';
import Heading from '@/components/heading';
import type { AdminCourse } from '@/types';

type Props = {
    course: Pick<AdminCourse, 'id' | 'code' | 'name'>;
};

export default function CoursesEdit({ course }: Props) {
    return (
        <>
            <Head title="Editar disciplina" />

            <div className="flex flex-1 flex-col p-4">
                <Heading
                    title="Editar disciplina"
                    description="Atualize os dados da disciplina selecionada."
                />
                <CourseForm
                    form={CourseController.update.form.patch(course.id)}
                    course={course}
                />
            </div>
        </>
    );
}

CoursesEdit.layout = {
    breadcrumbs: [
        { title: 'Disciplinas', href: CourseController.index() },
        { title: 'Editar disciplina', href: CourseController.index() },
    ],
};
