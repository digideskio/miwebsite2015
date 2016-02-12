# HOPS Stundenplan API
===


Andre Kasper hat uns eine kleine API für den Stundenplan gebaut. Yeah. Dieser ist nur aus dem FH Netz erreichbar. Ich hab aber eine kleine Bridge gebaut, damit mal auch von überall drauf zugreifen kann. Ist nicht besonders schnell, aber wir sollten die Daten eh cachen, da diese sich mega selten ändern.

# ToDos:
https://github.com/th-koeln/miwebsite2015/issues?q=is%3Aissue+is%3Aopen+label%3A%22HOPS+Modulverzeichnis%22


#Aufrufe:
	https://fhpwww.gm.fh-koeln.de/hops/api/modules.php
	

Gibt alle Module (ID, Bezeichnung, Studiengang und Schwerpunkt) aus. Schwerpunkt braucht ihr wahrscheinlich nicht, den gibt es nur beim Informatik-Master und bei den Ingenieuren. Aber wer weiß, wofür wir die API noch brauchen ;)

	https://fhpwww.gm.fh-koeln.de/hops/api/modules.php?program=MI_B
wäre zum Beispiel der Medieninformatik-Bachelor als Filter auf den Studiengang (MI_M wäre dann der Master). Filter für den Schwerpunkt wäre

	https://fhpwww.gm.fh-koeln.de/hops/api/modules.php?program=I_M&emphasis=SOF 
als Beispiel für den Informatik-Master, Schwerpunkt Software Engineering

	https://fhpwww.gm.fh-koeln.de/hops/api/moduleDetails.php?mid=
erwartet hinter mid noch die ID, also z. B.

	https://fhpwww.gm.fh-koeln.de/hops/api/moduleDetails.php?mid=1930

Hier gebe ich momentan alles (!!!) aus, also wirklich alles :) Es werden nicht (mehr) alle Spalten in den Anwendungen bei uns benötigt und sind teilweise nur noch aus Gründen der Abwärtskompatibilität vorhanden. Das wird sich aber noch im Laufe dieser Woche klären, dann passe ich die Ausgabe hier noch an und gebe dir kurz Bescheid.

Ansonsten sind da auch noch die Daten vom dazugehörigen Fach dabei. Die kann ich aber auch noch "rauswerfen", falls dir das zu unübersichtlich ist?! 

#Zugriff über die Bridge

Die Brigde bildet die beiden API Methoden ab: 


	/api/modules.php -> www.medieninformatik.th-koeln.de/dev/api-bridge.php?modus=modules
	/api/moduleDetails.php -> www.medieninformatik.th-koeln.de/dev/api-bridge.php?modus=details
	
Alle anderen Parameter können einfach angehangen werden, z.B.

	http://www.medieninformatik.th-koeln.de/dev/api-bridge.php?modus=details&mid=1930