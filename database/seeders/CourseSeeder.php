<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $courses = [
            'IMD0012' => 'Introdução às Técnicas de Programação',
            'IMD0017' => 'Práticas de Leitura e Escrita em Português I',
            'IMD0018' => 'Práticas de Leitura em Inglês',
            'IMD0019' => 'Resolução de Problemas Matemáticos para TI',
            'IMD0020' => 'Tecnologia da Informação e Sociedade',
            'IMD0024' => 'Cálculo Diferencial e Integral I',
            'IMD0027' => 'Práticas de Leitura e Escrita em Português II',
            'IMD0028' => 'Fundamentos Matemáticos da Computação I',
            'IMD0029' => 'Estrutura de Dados Básicas I',
            'IMD0030' => 'Linguagem de Programação I',
            'IMD0033' => 'Probabilidade',
            'IMD0034' => 'Vetores e Geometria Analítica',
            'IMD0038' => 'Fundamentos Matemáticos da Computação II',
            'IMD0041' => 'Introdução à Organização e Arquitetura de Computadores',
        ];

        foreach ($courses as $code => $name) {
            Course::query()->updateOrCreate(['code' => $code], ['name' => $name]);
        }
    }
}
