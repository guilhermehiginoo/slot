import { Form } from '@inertiajs/react';
import CourseArchiveController from '@/actions/App/Http/Controllers/Admin/CourseArchiveController';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
} from '@/components/ui/dialog';
import type { AdminCourse } from '@/types';

type Props = {
    course: AdminCourse | null;
    onClose: () => void;
};

export function ArchiveCourseDialog({ course, onClose }: Props) {
    return (
        <Dialog
            open={course !== null}
            onOpenChange={(open) => !open && onClose()}
        >
            <DialogContent>
                <DialogTitle>
                    A disciplina deixará de aparecer nas buscas e não aceitará
                    novos horários. O histórico será mantido.
                </DialogTitle>
                {course && (
                    <p className="text-sm font-medium">
                        {course.code} — {course.name}
                    </p>
                )}
                <DialogDescription>
                    Não será possível criar novos agendamentos para esta
                    disciplina.
                </DialogDescription>

                {course && (
                    <Form
                        {...CourseArchiveController.store.form(course.id)}
                        options={{ preserveScroll: true }}
                        onSuccess={onClose}
                    >
                        {({ processing }) => (
                            <DialogFooter className="gap-2">
                                <DialogClose asChild>
                                    <Button variant="outline">Cancelar</Button>
                                </DialogClose>
                                <Button
                                    type="submit"
                                    variant="destructive"
                                    disabled={processing}
                                >
                                    Arquivar disciplina
                                </Button>
                            </DialogFooter>
                        )}
                    </Form>
                )}
            </DialogContent>
        </Dialog>
    );
}
