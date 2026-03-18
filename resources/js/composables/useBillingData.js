// useBillingData.js
// Composable para datos de facturación SAT México
// Selectores encadenados: Tipo Persona → Uso CFDI → Régimen Fiscal

import { ref, computed } from 'vue'

// ─── Catálogos SAT ────────────────────────────────────────────────────────────

const REGIMENES = [
    { codigo: '601', descripcion: 'General de Ley Personas Morales', persona: ['moral'] },
    { codigo: '603', descripcion: 'Personas Morales con Fines no Lucrativos', persona: ['moral'] },
    { codigo: '605', descripcion: 'Sueldos y Salarios e Ingresos Asimilados a Salarios', persona: ['fisica'] },
    { codigo: '606', descripcion: 'Arrendamiento', persona: ['fisica'] },
    { codigo: '607', descripcion: 'Régimen de Enajenación o Adquisición de Bienes', persona: ['fisica'] },
    { codigo: '608', descripcion: 'Demás ingresos', persona: ['fisica'] },
    { codigo: '610', descripcion: 'Residentes en el Extranjero sin Establecimiento Permanente en México', persona: ['fisica', 'moral'] },
    { codigo: '611', descripcion: 'Ingresos por Dividendos (socios y accionistas)', persona: ['fisica'] },
    { codigo: '612', descripcion: 'Personas Físicas con Actividades Empresariales y Profesionales', persona: ['fisica'] },
    { codigo: '614', descripcion: 'Ingresos por intereses', persona: ['fisica'] },
    { codigo: '615', descripcion: 'Régimen de los ingresos por obtención de premios', persona: ['fisica'] },
    { codigo: '616', descripcion: 'Sin obligaciones fiscales', persona: ['fisica'] },
    { codigo: '620', descripcion: 'Sociedades Cooperativas de Producción que optan por diferir sus ingresos', persona: ['moral'] },
    { codigo: '621', descripcion: 'Incorporación Fiscal', persona: ['fisica'] },
    { codigo: '622', descripcion: 'Actividades Agrícolas, Ganaderas, Silvícolas y Pesqueras', persona: ['moral'] },
    { codigo: '623', descripcion: 'Opcional para Grupos de Sociedades', persona: ['moral'] },
    { codigo: '624', descripcion: 'Coordinados', persona: ['moral'] },
    { codigo: '625', descripcion: 'Régimen de las Actividades Empresariales con ingresos a través de Plataformas Tecnológicas', persona: ['fisica'] },
    { codigo: '626', descripcion: 'Régimen Simplificado de Confianza (RESICO)', persona: ['fisica', 'moral'] },
]

const USOS_CFDI = [
    {
        codigo: 'G01', descripcion: 'Adquisición de mercancías',
        fisica: true, moral: true,
        regimenes: ['601', '603', '606', '612', '620', '621', '622', '623', '624', '625', '626'],
    },
    {
        codigo: 'G02', descripcion: 'Devoluciones, descuentos o bonificaciones',
        fisica: true, moral: true,
        regimenes: ['601', '603', '606', '612', '616', '620', '621', '622', '623', '624', '625', '626'],
    },
    {
        codigo: 'G03', descripcion: 'Gastos en general',
        fisica: true, moral: true,
        regimenes: ['601', '603', '606', '612', '620', '621', '622', '623', '624', '625', '626'],
    },
    {
        codigo: 'I01', descripcion: 'Construcciones',
        fisica: true, moral: true,
        regimenes: ['601', '603', '606', '612', '620', '621', '622', '623', '624', '625', '626'],
    },
    {
        codigo: 'I02', descripcion: 'Mobiliario y equipo de oficina por inversiones',
        fisica: true, moral: true,
        regimenes: ['601', '603', '606', '612', '620', '621', '622', '623', '624', '625', '626'],
    },
    {
        codigo: 'I03', descripcion: 'Equipo de transporte',
        fisica: true, moral: true,
        regimenes: ['601', '603', '606', '612', '620', '621', '622', '623', '624', '625', '626'],
    },
    {
        codigo: 'I04', descripcion: 'Equipo de cómputo y accesorios',
        fisica: true, moral: true,
        regimenes: ['601', '603', '606', '612', '620', '621', '622', '623', '624', '625', '626'],
    },
    {
        codigo: 'I05', descripcion: 'Dados, troqueles, moldes, matrices y herramental',
        fisica: true, moral: true,
        regimenes: ['601', '603', '606', '612', '620', '621', '622', '623', '624', '625', '626'],
    },
    {
        codigo: 'I06', descripcion: 'Comunicaciones telefónicas',
        fisica: true, moral: true,
        regimenes: ['601', '603', '606', '612', '620', '621', '622', '623', '624', '625', '626'],
    },
    {
        codigo: 'I07', descripcion: 'Comunicaciones satelitales',
        fisica: true, moral: true,
        regimenes: ['601', '603', '606', '612', '620', '621', '622', '623', '624', '625', '626'],
    },
    {
        codigo: 'I08', descripcion: 'Otra maquinaria y equipo',
        fisica: true, moral: true,
        regimenes: ['601', '603', '606', '612', '620', '621', '622', '623', '624', '625', '626'],
    },
    {
        codigo: 'D01', descripcion: 'Honorarios médicos, dentales y gastos hospitalarios',
        fisica: true, moral: false,
        regimenes: ['605', '606', '608', '611', '612', '614', '607', '615', '625'],
    },
    {
        codigo: 'D02', descripcion: 'Gastos médicos por incapacidad o discapacidad',
        fisica: true, moral: false,
        regimenes: ['605', '606', '608', '611', '612', '614', '607', '615', '625'],
    },
    {
        codigo: 'D03', descripcion: 'Gastos funerales',
        fisica: true, moral: false,
        regimenes: ['605', '606', '608', '611', '612', '614', '607', '615', '625'],
    },
    {
        codigo: 'D04', descripcion: 'Donativos',
        fisica: true, moral: false,
        regimenes: ['605', '606', '608', '611', '612', '614', '607', '615', '625'],
    },
    {
        codigo: 'D05', descripcion: 'Intereses reales efectivamente pagados por créditos hipotecarios (casa habitación)',
        fisica: true, moral: false,
        regimenes: ['605', '606', '608', '611', '612', '614', '607', '615', '625'],
    },
    {
        codigo: 'D06', descripcion: 'Aportaciones voluntarias al SAR',
        fisica: true, moral: false,
        regimenes: ['605', '606', '608', '611', '612', '614', '607', '615', '625'],
    },
    {
        codigo: 'D07', descripcion: 'Primas por seguros de gastos médicos',
        fisica: true, moral: false,
        regimenes: ['605', '606', '608', '611', '612', '614', '607', '615', '625'],
    },
    {
        codigo: 'D08', descripcion: 'Gastos de transportación escolar obligatoria',
        fisica: true, moral: false,
        regimenes: ['605', '606', '608', '611', '612', '614', '607', '615', '625'],
    },
    {
        codigo: 'D09', descripcion: 'Depósitos en cuentas para el ahorro, primas que tengan como base planes de pensiones',
        fisica: true, moral: false,
        regimenes: ['605', '606', '608', '611', '612', '614', '607', '615', '625'],
    },
    {
        codigo: 'D10', descripcion: 'Pagos por servicios educativos (colegiaturas)',
        fisica: true, moral: false,
        regimenes: ['605', '606', '608', '611', '612', '614', '607', '615', '625'],
    },
    {
        codigo: 'S01', descripcion: 'Sin efectos fiscales',
        fisica: true, moral: true,
        regimenes: ['601', '603', '605', '606', '608', '610', '611', '612', '614', '616', '620', '621', '622', '623', '624', '607', '615', '625', '626'],
    },
    {
        codigo: 'CP01', descripcion: 'Pagos',
        fisica: true, moral: true,
        regimenes: ['601', '603', '605', '606', '608', '610', '611', '612', '614', '616', '620', '621', '622', '623', '624', '607', '615', '625', '626'],
    },
    {
        codigo: 'CN01', descripcion: 'Nómina',
        fisica: true, moral: false,
        regimenes: ['605'],
    },
]

const RFC_REGEX = /^([A-ZÑ&]{3,4}) ?(?:- ?)?(\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01])) ?(?:- ?)?([A-Z\d]{2})([A-Z\d])$/;

const CP_REGEX = /^\d{5}$/;

// ─── Composable ───────────────────────────────────────────────────────────────

export function useBillingData(form) {
    // ── Opciones de tipo persona (estáticas) ──────────────────────────────────
    const tiposPersona = [
        { value: 'fisica', label: 'Persona Física' },
        { value: 'moral', label: 'Persona Moral' },
    ]

    // ── Usos CFDI filtrados por tipo de persona ────────────────────────────────
    const usosCfdiDisponibles = computed(() => {
        if (!form.value.tax_person_type) return []
        return USOS_CFDI.filter(uso => 
            form.value.tax_person_type === 'fisica' ? uso.fisica : uso.moral
        )
    })

    // ── Regímenes disponibles según uso CFDI seleccionado y tipo persona ──────
    const regimenesDisponibles = computed(() => {

        if (!form.value.cfdi_use) return []

        const uso = USOS_CFDI.find(u => u.codigo === form.value.cfdi_use)
        if (!uso) return []

        return REGIMENES.filter(r => 
            uso.regimenes.includes(r.codigo) && 
            r.persona.includes(form.value.tax_person_type)
        )
    })

    const isRfcValid = computed(() => {
        const rfc = form.value.rfc?.toUpperCase() || '';
        if (!rfc) return true; // No mostrar error si está vacío
        return RFC_REGEX.test(rfc);
    });

    const isCpValid = computed(() => {
        const cp = form.value.postal_code || '';
        if (!cp) return true;
        return CP_REGEX.test(cp);
    });

    // Formateador automático (opcional)
    const formatRfc = () => {
        if (form.value.rfc) {
            form.value.rfc = form.value.rfc.toUpperCase().trim();
        }
    };

    function setPersonType(value) {
        form.value.tax_person_type = value
        form.value.cfdi_use = ''
        form.value.tax_regime = ''
    }

    function setCfdiUse(codigo) {
        form.value.cfdi_use = codigo
        form.value.tax_regime = ''
    }

    return {
        // Opciones
        tiposPersona,
        usosCfdiDisponibles,
        regimenesDisponibles,
        isRfcValid,
        isCpValid,
        
        // Acciones
        setPersonType,
        setCfdiUse,
        formatRfc,
    }
}