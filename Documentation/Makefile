#
# Documentation Makefile
#

RSTFILES := $(shell find . -name '*.rst')
HTMLFILES := $(addsuffix .html, $(basename ${RSTFILES}))

DOCURL=http://dev.nethesis.it/dpdev/Documentation/Api/


RST2HTML=rst2html --stylesheet-path=css/style.css --link-stylesheet

PDEPEND_OUTPUT=Metrics/pyramid.svg Metrics/jdepend.svg Metrics/summary.xml
API_OUTPUT=Api/index.htm Api/Nethgui.svg


PHPUML_OPTS = --pure-object

ifndef FULL_API
PHPUML_OPTS += --only-api
endif

%.html: %.rst
	${RST2HTML} $< $@

.PHONY: clean all

all: ${HTMLFILES} ${PDEPEND_OUTPUT} 

api: ${API_OUTPUT}

Api/index.htm:
	phpuml -f htmlnew ${PHPUML_OPTS}  -n 'Nethgui framework' -o Api ../Nethgui

Api/Nethgui.svg:
	phpuml -x 2 ${PHPUML_OPTS} ../Nethgui | xsltproc --param docUrl 'string("${DOCURL}")' XmiToDot.xsl - | dot -Tsvg > Api/Nethgui.svg

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
