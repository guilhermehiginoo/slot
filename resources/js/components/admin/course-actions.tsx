import { Button } from '@/components/ui/button';
import type { AdminCourse } from '@/types';

type Props = {
    course: AdminCourse;
    onEdit: (course: AdminCourse) => void;
    onArchive: (course: AdminCourse) => void;
};

export function CourseActions({ course, onEdit, onArchive }: Props) {
    const archived = course.archived_at !== null;

    return (
        <div className="grid grid-cols-2 gap-2 md:flex md:gap-4">
            <Button
                variant="outline"
                className="h-11 md:h-auto md:border-0 md:bg-transparent md:p-0 md:font-semibold md:text-primary md:shadow-none md:hover:underline"
                onClick={() => onEdit(course)}
            >
                Editar
            </Button>
            <Button
                variant="outline"
                className="h-11 md:h-auto md:border-0 md:bg-transparent md:p-0 md:font-normal md:text-muted-foreground md:shadow-none md:hover:underline md:disabled:opacity-100"
                disabled={archived}
                onClick={() => onArchive(course)}
            >
                {archived ? 'Arquivada' : 'Arquivar'}
            </Button>
        </div>
    );
}
