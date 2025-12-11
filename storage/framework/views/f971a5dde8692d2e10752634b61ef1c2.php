<?php
    $valorCampo = $operacion->valoresCamposPersonalizados->where('campo_personalizado_id', $campo->id)->first();
    $valorMostrar = $valorCampo ? $valorCampo->valor : '-';
    if ($campo->tipo === 'fecha' && $valorCampo && $valorCampo->valor) {
        try {
            $valorMostrar = \Carbon\Carbon::parse($valorCampo->valor)->format('d/m/Y');
        } catch (\Exception $e) {
            $valorMostrar = $valorCampo->valor;
        }
    }
?>
<td class="px-3 py-4 border-r border-slate-200 text-slate-600 bg-indigo-50/30 campo-personalizado-cell" 
    data-campo-id="<?php echo e($campo->id); ?>" 
    data-operacion-id="<?php echo e($operacion->id); ?>">
    <div class="flex items-center justify-between">
        <span class="valor-campo"><?php echo e($valorMostrar); ?></span>
        <button onclick="editarCampoPersonalizado(<?php echo e($operacion->id); ?>, <?php echo e($campo->id); ?>, '<?php echo e($campo->tipo); ?>', '<?php echo e(addslashes($campo->nombre)); ?>')" 
                class="text-indigo-400 hover:text-indigo-600 ml-2" title="Editar">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
            </svg>
        </button>
    </div>
</td>
<?php /**PATH C:\Users\SISTEMAS\Downloads\ERP EstrategiaeInnovacion\Sistema_Tickets_E-I\resources\views/Logistica/partials/campo-personalizado-celda.blade.php ENDPATH**/ ?>