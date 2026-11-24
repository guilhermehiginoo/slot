import { Form } from '@inertiajs/react';
import { useState } from 'react';
import ClassGroupController from '@/actions/App/Http/Controllers/Admin/ClassGroupController';
import { ProfessorPickerDialog } from '@/components/admin/professor-picker-dialog';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { AdminClassGroup, CourseOption, ProfessorOption } from '@/types';

type Props = {
    open: boolean;
    classGroup: AdminClassGroup | null;
    courses: CourseOption[];
    professors: ProfessorOption[];
    currentSemester: string;
    onClose: () => void;
};

export function ClassGroupFormDialog({
    open,
    classGroup,
    courses,
    professors,
    currentSemester,
    onClose,
}: Props) {
    const form =
        classGroup === null
            ? ClassGroupController.store.form()
            : ClassGroupController.update.form.patch(classGroup.id);

    const courseOptions =
        classGroup === null ||
        courses.some((course) => course.id === classGroup.course.id)
            ? courses
            : [classGroup.course, ...courses];

    return (
        <Dialog open={open} onOpenChange={(isOpen) => !isOpen && onClose()}>
            <DialogContent className="max-h-[calc(100dvh-2rem)] overflow-y-auto bg-card md:max-w-2xl">
                <DialogTitle>
                    {classGroup === null ? 'Nova turma' : 'Editar turma'}
                </DialogTitle>
                <DialogDescription>
                    Vincule a disciplina aos professores deste semestre.
                </DialogDescription>

                <Form
                    key={classGroup?.id ?? 'new'}
                    {...form}
                    options={{ preserveScroll: true, preserveState: true }}
                    onSuccess={onClose}
                    className="flex flex-col gap-6"
                >
                    {({ processing, errors }) => (
                        <ClassGroupFields
                            classGroup={classGroup}
                            courses={courseOptions}
                            professors={professors}
                            currentSemester={currentSemester}
                            errors={errors}
                            processing={processing}
                        />
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}

type FieldsProps = {
    classGroup: AdminClassGroup | null;
    courses: CourseOption[];
    professors: ProfessorOption[];
    currentSemester: string;
    errors: Record<string, string>;
    processing: boolean;
};

function ClassGroupFields({
    classGroup,
    courses,
    professors,
    currentSemester,
    errors,
    processing,
}: FieldsProps) {
    const [selectedProfessors, setSelectedProfessors] = useState<number[]>(
        classGroup?.professors.map((professor) => professor.id) ?? [],
    );

    const professorsError =
        errors.professors ??
        Object.entries(errors).find(([field]) =>
            field.startsWith('professors.'),
        )?.[1];

    return (
        <>
            <div className="grid gap-6 md:grid-cols-2">
                <div className="grid grid-cols-1 content-start gap-2">
                    <Label htmlFor="course_id">Disciplina</Label>
                    <Select
                        name="course_id"
                        defaultValue={classGroup?.course.id.toString()}
                    >
                        <SelectTrigger
                            id="course_id"
                            className="h-11 w-full"
                            aria-invalid={errors.course_id !== undefined}
                        >
                            <SelectValue placeholder="Selecione a disciplina" />
                        </SelectTrigger>
                        <SelectContent>
                            {courses.map((course) => (
                                <SelectItem
                                    key={course.id}
                                    value={course.id.toString()}
                                >
                                    {course.code} · {course.name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <InputError message={errors.course_id} />
                </div>

                <div className="grid grid-cols-1 content-start gap-2">
                    <Label htmlFor="semester">Semestre</Label>
                    <Input
                        id="semester"
                        name="semester"
                        defaultValue={classGroup?.semester ?? currentSemester}
                        aria-describedby="semester-hint"
                        className="h-11"
                        required
                    />
                    <p
                        id="semester-hint"
                        className="text-sm text-muted-foreground"
                    >
                        Formato: ano.1 ou ano.2
                    </p>
                    <InputError message={errors.semester} />
                </div>

                <div className="grid grid-cols-1 content-start gap-2">
                    <Label htmlFor="number">Número da turma</Label>
                    <Input
                        id="number"
                        name="number"
                        defaultValue={classGroup?.number}
                        placeholder="Ex.: T01"
                        className="h-11"
                        required
                    />
                    <InputError message={errors.number} />
                </div>

                <div className="grid grid-cols-1 content-start gap-2">
                    <Label htmlFor="professors">Professores</Label>
                    <ProfessorPickerDialog
                        id="professors"
                        professors={professors}
                        selected={selectedProfessors}
                        invalid={professorsError !== undefined}
                        onApply={setSelectedProfessors}
                    />
                    {selectedProfessors.map((id) => (
                        <input
                            key={id}
                            type="hidden"
                            name="professors[]"
                            value={id}
                        />
                    ))}
                    <InputError message={professorsError} />
                </div>

                <div className="grid grid-cols-1 content-start gap-2">
                    <Label htmlFor="schedule">Código de horário</Label>
                    <Input
                        id="schedule"
                        name="schedule"
                        defaultValue={classGroup?.schedule}
                        placeholder="Ex.: 35M12"
                        aria-describedby="schedule-hint"
                        className="h-11"
                        maxLength={50}
                        required
                    />
                    <p
                        id="schedule-hint"
                        className="text-sm text-muted-foreground"
                    >
                        Código exibido aos alunos.
                    </p>
                    <InputError message={errors.schedule} />
                </div>
            </div>

            <DialogFooter className="gap-2">
                <DialogClose asChild>
                    <Button type="button" variant="outline">
                        Cancelar
                    </Button>
                </DialogClose>
                <Button type="submit" disabled={processing}>
                    Salvar turma
                </Button>
            </DialogFooter>
        </>
    );
}
