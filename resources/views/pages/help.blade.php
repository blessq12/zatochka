<x-app-layout title="Помощь">
    <!-- Hero секция -->
    <x-page-hero title="Справочный <span class='text-accent'>центр</span>"
        description="Здесь вы найдете ответы на часто задаваемые вопросы и инструкции по использованию нашего сайта."
        :breadcrumbs="[['name' => 'Помощь', 'href' => route('help')]]" />

    <!-- Vue компонент для интерактивного контента -->
    <div id="help-content">
        <help-page></help-page>
    </div>
</x-app-layout>
