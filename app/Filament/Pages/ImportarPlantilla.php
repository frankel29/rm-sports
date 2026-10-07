<?php

namespace App\Filament\Pages;

use App\Domain\Importacion\DTO\ImportacionResultado;
use App\Domain\Importacion\ImportarPlantillaService;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

class ImportarPlantilla extends Page
{
    protected string $view = 'filament.pages.importar-plantilla';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static UnitEnum|string|null $navigationGroup = 'Herramientas';

    protected static ?string $navigationLabel = 'Importar plantilla';

    protected static ?string $title = 'Importar plantilla de datos iniciales';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->esAdminGeneral();
    }

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    /**
     * @var array<string, ImportacionResultado>|null
     */
    public ?array $resultados = null;

    public bool $confirmado = false;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('archivo')
                    ->label('Plantilla Excel (.xlsx)')
                    ->disk('local')
                    ->directory('importaciones-temp')
                    ->visibility('private')
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ])
                    ->required(),
            ])
            ->statePath('data');
    }

    public function previsualizar(): void
    {
        $ruta = $this->rutaArchivoSubido();

        if (! $ruta) {
            return;
        }

        $this->resultados = app(ImportarPlantillaService::class)->previsualizarTodo($ruta, auth()->user());
        $this->confirmado = false;
    }

    public function confirmar(): void
    {
        $ruta = $this->rutaArchivoSubido();

        if (! $ruta) {
            return;
        }

        $this->resultados = app(ImportarPlantillaService::class)->confirmarTodo($ruta, auth()->user());

        $huboErrores = collect($this->resultados)->contains(fn (ImportacionResultado $r) => $r->conError());

        if ($huboErrores) {
            $this->confirmado = false;

            Notification::make()
                ->title('La importación tuvo errores y no se guardó nada')
                ->body('Corrija las filas señaladas y vuelva a intentar.')
                ->danger()
                ->send();

            return;
        }

        $this->confirmado = true;

        Notification::make()
            ->title('Importación confirmada correctamente')
            ->success()
            ->send();
    }

    private function rutaArchivoSubido(): ?string
    {
        $state = $this->form->getState();
        $archivo = $state['archivo'] ?? null;

        if (! $archivo) {
            Notification::make()
                ->title('Suba primero un archivo .xlsx')
                ->warning()
                ->send();

            return null;
        }

        return Storage::disk('local')->path($archivo);
    }
}
