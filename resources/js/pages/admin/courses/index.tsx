import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';

export default function CoursesIndex() {
    return (
        <>
            <Head title="Disciplinas" />

            <div className="flex flex-1 flex-col gap-6 p-4">
                <Heading
                    title="Disciplinas"
                    description="Gerencie as disciplinas disponíveis para monitoria."
                />
            </div>
        </>
    );
}