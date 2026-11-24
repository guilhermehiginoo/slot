import { Form } from '@inertiajs/react';
import CourseController from '@/actions/App/Http/Controllers/Admin/CourseController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { AdminCourse } from '@/types';

type Props = {
    open: boolean;
    course: AdminCourse | null;
    onClose: () => void;
};

export function CourseFormDialog({ open, course, onClose }: Props) {
    const form =
        course === null
            ? CourseController.store.form()
            : CourseController.update.form.patch(course.id);

    return (
        <Dialog open={open} onOpenChange={(isOpen) => !isOpen && onClose()}>
            <DialogContent className="bg-card">
                <DialogTitle>
                    {course === null ? 'Nova disciplina' : 'Editar disciplina'}
                </DialogTitle>
                <DialogDescription>
                    {course === null
                        ? 'Cadastre uma disciplina no catálogo acadêmico.'
                        : 'Atualize os dados da disciplina selecionada.'}
                </DialogDescription>

                <Form
                    key={course?.id ?? 'new'}
                    {...form}
                    options={{ preserveScroll: true, preserveState: true }}
                    onSuccess={onClose}
                    className="flex flex-col gap-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="code">Código</Label>
                                <Input
                                    id="code"
                                    name="code"
                                    defaultValue={course?.code}
                                    placeholder="Ex.: IMD0030"
                                    aria-describedby="code-hint"
                                    required
                                />
                                <p
                                    id="code-hint"
                                    className="text-sm text-muted-foreground"
                                >
                                    Identificador oficial da disciplina no IMD.
                                </p>
                                <InputError message={errors.code} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="name">Nome</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    defaultValue={course?.name}
                                    placeholder="Ex.: Linguagem de Programação I"
                                    required
                                />
                                <InputError message={errors.name} />
                            </div>

                            <DialogFooter className="gap-2">
                                <DialogClose asChild>
                                    <Button variant="outline">Cancelar</Button>
                                </DialogClose>
                                <Button type="submit" disabled={processing}>
                                    Salvar disciplina
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
