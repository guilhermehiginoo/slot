import { Form, Link } from '@inertiajs/react';
import CourseController from '@/actions/App/Http/Controllers/Admin/CourseController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { AdminCourse } from '@/types';
import type { RouteFormDefinition } from '@/wayfinder';

type Props = {
    form: RouteFormDefinition<'post'>;
    course?: Pick<AdminCourse, 'code' | 'name'>;
};

export function CourseForm({ form, course }: Props) {
    return (
        <Form {...form} className="flex max-w-xl flex-col gap-6">
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

                    <div className="flex flex-col-reverse gap-2 md:flex-row md:justify-end">
                        <Button variant="outline" asChild>
                            <Link href={CourseController.index()}>
                                Cancelar
                            </Link>
                        </Button>
                        <Button type="submit" disabled={processing}>
                            Salvar disciplina
                        </Button>
                    </div>
                </>
            )}
        </Form>
    );
}
