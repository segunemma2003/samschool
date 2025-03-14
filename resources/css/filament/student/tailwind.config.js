import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/Ourstudent/**/*.php',
        './resources/views/filament/ourstudent/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
         './app/Filament/**/*.php',
        './resources/views/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
         './vendor/diogogpinto/filament-auth-ui-enhancer/resources/**/*.blade.php',

    ],
}
