import stanza 
stanza.download('es', package='ancora', processors='tokenize,mwt,pos,lemma', verbose=True) 
stNLP = stanza.Pipeline(processors='tokenize,mwt,pos,lemma', lang='es', use_gpu=True) 
doc = stNLP('Barack Obama nació en Hawaii.') 
print(*[f'word: {word.text+" "}\tlemma: {word.lemma}' for sent in doc.sentences for word in sent.words], sep='\n')