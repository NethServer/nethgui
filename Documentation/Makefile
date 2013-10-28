#
# Documentation Makefile
#

RSTFILES 	:= $(shell find . -name '*.rst')
HTMLFILES	:= $(addsuffix .html, $(basename ${RSTFILES}))
DOCURL		?= http://dev.nethesis.it/nethgui/Documentation/Api/
RST2HTML	:= rst2html --stylesheet-path=css/style.css --link-stylesheet
PDEPEND_OUTPUT	:= Metrics/pyramid.svg Metrics/jdepend.svg Metrics/summary.xml
API_OUTPUT	:= Api/index.html Api/Nethgui.xmi Api/Nethgui.dot Api/Nethgui.svg
PHPUML_OPTS	:= --pure-object

ifndef FULL_API
PHPUML_OPTS += --only-api
endif

%.html: %.rst
	${RST2HTML} $< $@

.PHONY: clean all sync

all: ${HTMLFILES} ${PDEPEND_OUTPUT} 

api: ${API_OUTPUT}

Api/index.html:
	apigen -s ../Nethgui/ --main 'Nethgui' --title 'Nethgui Framework' -d Api/

Api/Nethgui.xmi:
	phpuml -x 2 ${PHPUML_OPTS} ../Nethgui > $@

Api/Nethgui.dot: Api/Nethgui.xmi XmiToDot.xsl
	xsltproc --param docUrl 'string("${DOCURL}")' XmiToDot.xsl $< > $@

Api/Nethgui.svg: Api/Nethgui.dot 
	dot -Tsvg $< > Api/Nethgui.svg

sync:
	@if [ -z "$(SYNC_DEST)" ]; then echo "Define SYNC_DEST variable. Ex: make sync SYNC_DEST=hostname:/abspath/"; exit 1; fi
	rsync -Caz -stats --delete ${PWD}/ $(SYNC_DEST)

clean:
	rm -f ${HTMLFILES} ${PDEPEND_OUTPUT} 
	rm -rf Api/* CodeCoverage/*

${PDEPEND_OUTPUT}:
	pdepend \
	  --summary-xml=Metrics/summary.xml  \
	  --jdepend-chart=Metrics/jdepend.svg  \
	  --overview-pyramid=Metrics/pyramid.svg  \
	  --ignore=../Nethgui/Test,../Nethgui/Language \
	  ../Nethgui/
