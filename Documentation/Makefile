#
# Documentation Makefile
#

RSTFILES := $(shell find . -name '*.rst')
HTMLFILES := $(addsuffix .html, $(basename ${RSTFILES}))

DOCURL=http://dev.nethesis.it/nethgui/Documentation/Api/


RST2HTML=rst2html --stylesheet-path=css/style.css --link-stylesheet

PDEPEND_OUTPUT=Metrics/pyramid.svg Metrics/jdepend.svg Metrics/summary.xml
API_OUTPUT=Api/index.htm Api/Nethgui.xmi Api/Nethgui.dot Api/Nethgui.svg

PHPUML_OPTS = --pure-object

ifndef FULL_API
PHPUML_OPTS += --only-api
endif

%.html: %.rst
	${RST2HTML} $< $@

.PHONY: clean all sync

all: ${HTMLFILES} ${PDEPEND_OUTPUT} 

api: ${API_OUTPUT}

Api/index.htm:
	phpuml -f htmlnew ${PHPUML_OPTS}  -n 'Nethgui framework' -o Api ../Nethgui

Api/Nethgui.xmi:
	phpuml -x 2 ${PHPUML_OPTS} ../Nethgui > $@

Api/Nethgui.dot: Api/Nethgui.xmi XmiToDot.xsl
	xsltproc --param docUrl 'string("${DOCURL}")' XmiToDot.xsl $< > $@

Api/Nethgui.svg: Api/Nethgui.dot 
	dot -Tsvg $< > Api/Nethgui.svg

sync:
	rsync -zar -e "ssh -p 2222 -c blowfish -l git"  -stats ${PWD} git@dev.nethesis.it:/var/www/html/nethgui

clean:
	rm -f ${HTMLFILES} ${PDEPEND_OUTPUT}
	rm -rf Api/*

${PDEPEND_OUTPUT}:
	pdepend \
	  --summary-xml=Metrics/summary.xml  \
	  --jdepend-chart=Metrics/jdepend.svg  \
	  --overview-pyramid=Metrics/pyramid.svg  \
	  --ignore=../Nethgui/Language/ \
	  ../Nethgui/
