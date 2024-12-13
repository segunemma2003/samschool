<div>
    <!-- Livewire Component -->
    <livewire:teacher.student-result-details
        :record="1"
        :academic-year-id="($form->getState()['academic_year_id'] ?? null)"
        :term-id="($form->getState()['term_id'] ?? null)"
    />
</div>
