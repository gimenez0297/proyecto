CREATE TABLE public.unidades_medida (
	id_unidad serial NOT NULL,
	sigla varchar(10) NOT NULL,
	descripcion varchar(60) NOT NULL,
	situacion int2 NULL DEFAULT 1,
	CONSTRAINT unidades_medida_pkey PRIMARY KEY (id_unidad)
);

INSERT INTO public.unidades_medida (sigla, descripcion, situacion) VALUES('MM', 'MILIMETRO', 1);
INSERT INTO public.unidades_medida (sigla, descripcion, situacion) VALUES('M', 'METRO', 1);
INSERT INTO public.unidades_medida (sigla, descripcion, situacion) VALUES('NM', 'NANÓMETRO', 1);
INSERT INTO public.unidades_medida (sigla, descripcion, situacion) VALUES('MES', 'MES', 1);
INSERT INTO public.unidades_medida (sigla, descripcion, situacion) VALUES('MG', 'MILIGRAMO', 1);
INSERT INTO public.unidades_medida (sigla, descripcion, situacion) VALUES('NG', 'NANOGRAMO', 1);
INSERT INTO public.unidades_medida (sigla, descripcion, situacion) VALUES('M3', 'METROS CUBICOS', 1);
INSERT INTO public.unidades_medida (sigla, descripcion, situacion) VALUES('ML', 'MILILITRO', 1);
INSERT INTO public.unidades_medida (sigla, descripcion, situacion) VALUES('μM', 'MICRÓMETRO', 1);
INSERT INTO public.unidades_medida (sigla, descripcion, situacion) VALUES('μG', 'MICROGRAMO', 1);
INSERT INTO public.unidades_medida (sigla, descripcion, situacion) VALUES('μL', 'MICROLITRO', 1);
INSERT INTO public.unidades_medida (sigla, descripcion, situacion) VALUES('NN', 'SIN DEFINIR', 1);
INSERT INTO public.unidades_medida (sigla, descripcion, situacion) VALUES('M2', 'METROS CUADRADOS', 1);
INSERT INTO public.unidades_medida (sigla, descripcion, situacion) VALUES('DET.', 'DETERMINACION', 1);
INSERT INTO public.unidades_medida (sigla, descripcion, situacion) VALUES('KG.', 'KILOGRAMO', 1);
INSERT INTO public.unidades_medida (sigla, descripcion, situacion) VALUES('LB.', 'LIBRA', 1);
INSERT INTO public.unidades_medida (sigla, descripcion, situacion) VALUES('L', 'LITRO', 1);
INSERT INTO public.unidades_medida (sigla, descripcion, situacion) VALUES('TON.', 'TONELADA', 1);
INSERT INTO public.unidades_medida (sigla, descripcion, situacion) VALUES('UNI', 'UNIDAD', 1);